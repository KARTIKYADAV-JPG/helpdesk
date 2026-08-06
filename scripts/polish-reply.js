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

async function polish() {
    const apiKey = getApiKey();
    const preferredModel = process.env.GEMINI_MODEL || 'gemini-2.0-flash';
    const draftText = process.argv[2] || 'sorry for late update we are fixing the bug now and will update u soon';

    if (!apiKey) {
        console.error('Missing GEMINI_API_KEY');
        process.exit(1);
    }

    const systemPrompt = `You are a professional customer support assistant.
Your task is to polish and improve customer support draft replies.
Requirements:
1. Maintain the exact original meaning and factual content of the draft reply.
2. Make the tone professional, clear, polite, and well-structured.
3. Return ONLY the polished response text itself. Do NOT include markdown code blocks, quotes, prefixes, or commentary.`;

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
                prompt: `Draft Reply:\n${draftText}`,
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
                        { role: 'user', parts: [{ text: `${systemPrompt}\n\nDraft Reply:\n${draftText}` }] }
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

polish();
