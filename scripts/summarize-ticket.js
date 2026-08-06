import { generateText } from 'ai';
import { createGoogleGenerativeAI } from '@ai-sdk/google';
import fs from 'fs';
import path from 'path';

// Auto-load .env if process.env.GEMINI_API_KEY or GOOGLE_AI_API_KEY is not set or empty
const getApiKey = () => process.env.GEMINI_API_KEY || process.env.GOOGLE_AI_API_KEY || process.env.GOOGLE_GENAI_API_KEY;

if (!getApiKey() || getApiKey().trim() === '' || getApiKey() === 'null') {
    try {
        const envPath = path.resolve(process.cwd(), '.env');
        if (fs.existsSync(envPath)) {
            const envContent = fs.readFileSync(envPath, 'utf8');
            for (const line of envContent.split('\n')) {
                const trimmed = line.trim();
                if (trimmed && !trimmed.startsWith('#') && trimmed.includes('=')) {
                    const [key, ...valParts] = trimmed.split('=');
                    const k = key.trim();
                    const val = valParts.join('=').trim().replace(/^["']|["']$/g, '');
                    if (['GEMINI_API_KEY', 'GOOGLE_AI_API_KEY', 'GOOGLE_GENAI_API_KEY', 'GEMINI_MODEL'].includes(k)) {
                        process.env[k] = val;
                    }
                }
            }
        }
    } catch (e) {}
}

async function summarize() {
    const apiKey = getApiKey();
    const preferredModel = process.env.GEMINI_MODEL || 'gemini-2.0-flash';
    const payloadRaw = process.argv[2] || '{}';

    if (!apiKey) {
        console.error('Missing GEMINI_API_KEY');
        process.exit(1);
    }

    let payload = {};
    try {
        payload = JSON.parse(payloadRaw);
    } catch (e) {
        payload = { subject: 'Support Ticket', description: payloadRaw, replies: [] };
    }

    const { subject = '', description = '', replies = [] } = payload;

    const formattedReplies = replies.map((r, index) => 
        `Reply #${index + 1} (${r.senderType || r.sender_type || 'user'} - ${r.userName || 'User'}): ${r.body}`
    ).join('\n');

    const prompt = `Subject: ${subject}
Description: ${description}

Conversation History:
${formattedReplies.length > 0 ? formattedReplies : 'No replies yet.'}`;

    const systemPrompt = `You are a customer support AI assistant.
Your task is to summarize the ticket issue and its conversation history into a clear, concise bulleted or short paragraph summary (2-4 bullet points or sentences).
Focus on:
1. Core issue reported by the customer.
2. Key actions taken or responses provided so far.
3. Current status or next steps.
Return ONLY the clean summary text without markdown headers or fluff.`;

    const candidateModels = [...new Set([
        preferredModel,
        'gemini-2.0-flash',
        'gemini-2.5-flash',
        'gemini-2.0-flash-lite',
    ])];

    let lastError = null;

    for (const modelName of candidateModels) {
        // Method 1: Vercel AI SDK
        try {
            const google = createGoogleGenerativeAI({ apiKey });
            const { text } = await generateText({
                model: google(modelName),
                system: systemPrompt,
                prompt: prompt,
            });

            if (text && text.trim()) {
                console.log(text.trim());
                return;
            }
        } catch (error) {
            lastError = error;
        }

        // Method 2: Direct REST API fallback
        try {
            const url = `https://generativelanguage.googleapis.com/v1beta/models/${modelName}:generateContent?key=${apiKey}`;
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    contents: [
                        { role: 'user', parts: [{ text: `${systemPrompt}\n\n${prompt}` }] }
                    ]
                })
            });
            const data = await res.json();
            const responseText = data?.candidates?.[0]?.content?.parts?.[0]?.text;
            if (responseText && responseText.trim()) {
                console.log(responseText.trim());
                return;
            }
        } catch (fallbackErr) {
            lastError = fallbackErr;
        }
    }

    console.error('AI Error:', lastError ? (lastError.message || JSON.stringify(lastError)) : 'Failed to generate response.');
    process.exit(1);
}

summarize();
