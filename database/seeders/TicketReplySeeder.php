<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use App\Enums\TicketCategory;
use App\Enums\TicketStatus;
use App\Enums\TicketPriority;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TicketReplySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure ticket ID 110 exists
        $ticket = Ticket::find(110);

        if (!$ticket) {
            $customerUser = User::factory()->create([
                'name' => 'Alexander Wright',
                'email' => 'alexander.wright@enterprise.com',
                'role' => 'agent',
            ]);

            $agentUser = User::where('role', 'agent')->where('id', '!=', $customerUser->id)->first();
            if (!$agentUser) {
                $agentUser = User::factory()->create([
                    'name' => 'Sarah Connor (Infrastructure Lead)',
                    'email' => 'sarah.connor@helpdesk.com',
                    'role' => 'agent',
                ]);
            }

            $ticket = Ticket::create([
                'id' => 110,
                'ticket_number' => 'TKT-00110',
                'subject' => 'Critical Infrastructure Network Outage & High Latency Alert',
                'description' => "Hello Support Team,\n\nWe are experiencing severe network latency and sporadic packet drops across our secondary data center cluster in US-East. Multiple microservices are failing health checks and client connections are timing out.\n\nPlease investigate this issue immediately as it is affecting our production environment and paying customers.\n\nBest regards,\nAlexander Wright",
                'category' => TicketCategory::TECHNICAL_SUPPORT->value,
                'status' => TicketStatus::IN_PROGRESS->value,
                'priority' => TicketPriority::HIGH->value,
                'created_by' => $customerUser->id,
                'assigned_to' => $agentUser->id,
                'created_at' => Carbon::now()->subDays(5),
            ]);
        }

        $customer = User::find($ticket->created_by) ?? User::factory()->create(['name' => 'Alexander Wright']);
        $agent = User::find($ticket->assigned_to) ?? User::where('role', 'agent')->first() ?? User::factory()->create(['name' => 'Sarah Connor', 'role' => 'agent']);

        // Clear existing replies for ticket 110 to ensure exact count of 20
        $ticket->replies()->delete();

        // 2. 20 Realistic Conversations with 10+ lines each
        $conversations = [
            // Reply 1 (Customer)
            [
                'sender_type' => 'customer',
                'user_id' => $customer->id,
                'body' => "Hello Support Team,\nFollowing up on ticket #110 regarding our network latency issues in US-East.\nWe ran internal diagnostics on our core routers this morning.\nOur telemetry shows latency spikes reaching up to 850ms every 15 minutes.\nAs a result, our database cluster is repeatedly rejecting incoming pool connections.\nWe attached traceroute logs from node 10.0.4.12 for your review.\nNode us-east-gateway-01 seems to be dropping over 18% of packets.\nCould your network operations team verify BGP routes and switch configurations?\nThis is severely impacting our real-time customer dashboard and API clients.\nAwaiting your prompt update and root cause analysis.\nBest regards,\nAlexander Wright",
            ],
            // Reply 2 (Agent)
            [
                'sender_type' => 'agent',
                'user_id' => $agent->id,
                'body' => "Hello Alexander,\nThank you for providing the detailed traceroute logs and node telemetry.\nOur Network Operations Center (NOC) has escalated this ticket to Tier 2 Support.\nWe inspected us-east-gateway-01 and identified heavy hardware queue congestion.\nIt appears the primary optic link was undergoing automated failover.\nWe are currently rerouting incoming traffic through our redundant fiber backbone.\nBGP routing tables are actively propagating across regional edge nodes now.\nYou should observe latency stabilization within the next 10 to 15 minutes.\nOur engineers are running live packet capture probes on your dedicated subnet.\nPlease keep us updated if packet drops persist on your client nodes.\nBest regards,\nSarah Connor (Senior Infrastructure Agent)",
            ],
            // Reply 3 (Customer)
            [
                'sender_type' => 'customer',
                'user_id' => $customer->id,
                'body' => "Hi Sarah,\nThanks for the quick response and for rerouting traffic via the backup link.\nWe monitored our internal metrics for the last 15 minutes.\nLatency has dropped down from 850ms to approximately 120ms.\nHowever, we are still seeing intermittent HTTP 504 gateway timeouts on endpoint /api/v2/stream.\nOur load balancer error logs indicate that proxy connections are timing out after 30 seconds.\nCould you verify if the security firewall rules or rate limiters were reset during the failover?\nOur payment processing service relies heavily on this specific stream endpoint.\nWe need to ensure zero dropped connections before we resume full traffic.\nAttached is our latest server log snippet for your review.\nThanks again,\nAlexander",
            ],
            // Reply 4 (Agent)
            [
                'sender_type' => 'agent',
                'user_id' => $agent->id,
                'body' => "Hello Alexander,\nThank you for sharing the updated server log snippet.\nOur security engineering team investigated the firewall policy state during failover.\nWhen the backup fiber link engaged, the connection tracking table hit its max limit.\nThis caused the firewall to temporarily throttle long-lived HTTP streams on /api/v2/stream.\nWe have just doubled the connection tracking threshold on the edge firewall gateway.\nAdditionally, we Whitelisted your static IP pool to bypass rate limiting rules.\nCould you please trigger a test payload on /api/v2/stream and monitor latency?\nWe expect zero 504 timeouts with the new threshold in place.\nWe are keeping a dedicated engineer assigned to monitor your organization's traffic.\nStanding by for your test results.\nWarm regards,\nSarah Connor",
            ],
            // Reply 5 (Customer)
            [
                'sender_type' => 'customer',
                'user_id' => $customer->id,
                'body' => "Hi Sarah,\nWe just ran automated integration tests against the /api/v2/stream endpoint.\nOut of 5,000 requests sent over 5 minutes, 12 requests still failed with 502 Bad Gateway.\nWhile this is a significant improvement over earlier, it is still above our SLA threshold.\nHere is an example response header from one of the failed requests:\nHTTP/1.1 502 Bad Gateway - Connection Refused by Upstream Pool 10.0.4.88:8080.\nIt looks like one of the upstream worker nodes might be overwhelmed or unhealthy.\nCould you check if all 4 backend worker nodes in the US-East pool are online?\nWe want to make sure no dead worker node is receiving routed traffic.\nThanks for your ongoing dedication to resolving this.\nCheers,\nAlexander",
            ],
            // Reply 6 (Agent)
            [
                'sender_type' => 'agent',
                'user_id' => $agent->id,
                'body' => "Hi Alexander,\nGreat catch! You hit the nail on the head regarding backend worker node 10.0.4.88.\nOur health check monitoring revealed that node 10.0.4.88 crashed due to out-of-memory errors.\nThe load balancer was still forwarding 5% of traffic to it before health checks failed.\nWe have manually pulled node 10.0.4.88 out of the active load balancer pool.\nWe also spun up two fresh compute instances (10.0.4.91 and 10.0.4.92) to replace capacity.\nAll active backend nodes are now reporting 100% healthy status in our dashboard.\nPlease rerun your 5,000 request test suite whenever you are ready.\nWe expect 100% success rate across all streaming endpoints now.\nThank you for your patience while we tuned the node capacities.\nBest regards,\nSarah Connor",
            ],
            // Reply 7 (Customer)
            [
                'sender_type' => 'customer',
                'user_id' => $customer->id,
                'body' => "Hello Sarah,\nAwesome news! We re-ran the full test suite with 10,000 concurrent requests.\nAll 10,000 requests returned HTTP 200 OK with an average response time of 38ms.\nThe streaming endpoint is performing faster than it was prior to the incident.\nHowever, we have one remaining question regarding SSL certificates.\nDuring the failover, our security scanner flagged a temporary self-signed SSL cert error.\nDid the fallback gateway temporarily serve a backup certificate during transition?\nWe need to document this for our SOC 2 compliance audit logs.\nCould you provide a brief official statement regarding the SSL cert behavior?\nThis will help us clear our compliance review without issues.\nBest,\nAlexander",
            ],
            // Reply 8 (Agent)
            [
                'sender_type' => 'agent',
                'user_id' => $agent->id,
                'body' => "Hello Alexander,\nI am delighted to hear that response times are down to 38ms with 100% success rate!\nRegarding your query about the SSL certificate behavior during failover:\nYes, during the 45-second BGP switchover window, the secondary SSL proxy engaged.\nIt briefly presented a wildcard maintenance certificate before renewing your SAN domain cert.\nThis is expected behavior designed to prevent connection drops while certificates sync.\nHere is our formal compliance statement:\n'Helpdesk Infrastructure Incident Report #110: Brief SSL fallback cert served between 10:14 UTC and 10:15 UTC during emergency fiber BGP failover. Full TLS 1.3 encryption was maintained at all times.'\nYou can copy and paste this directly into your SOC 2 audit documentation.\nPlease let me know if your compliance team requires any further details.\nSincerely,\nSarah Connor",
            ],
            // Reply 9 (Customer)
            [
                'sender_type' => 'customer',
                'user_id' => $customer->id,
                'body' => "Hi Sarah,\nThank you so much for providing that official compliance statement!\nOur SOC 2 auditor accepted the explanation and closed the compliance flag.\nWe have one final check before we mark this ticket as resolved from our side.\nWe noticed that automated database backups scheduled for 12:00 UTC were delayed.\nCould you verify if the backup cron job was paused during the infrastructure maintenance?\nWe want to manually trigger a full database snapshot if the scheduled one was skipped.\nOur data retention policy requires daily snapshots without exceptions.\nCould you confirm the timestamp of the latest successful backup file on your storage bucket?\nAppreciate your help as always.\nRegards,\nAlexander",
            ],
            // Reply 10 (Agent)
            [
                'sender_type' => 'agent',
                'user_id' => $agent->id,
                'body' => "Hi Alexander,\nI checked our automated backup scheduler logs for your account database cluster.\nDuring the emergency gateway switch, the 12:00 UTC backup job was indeed paused.\nThis was done automatically by the system to prevent snapshot I/O locking during high network traffic.\nHowever, the backup daemon automatically resumed and completed a full snapshot at 12:45 UTC.\nHere are the details of the latest backup file:\n- File Name: db_snapshot_cluster_110_20260720_124500.tar.gz\n- Size: 14.8 GB\n- Verification Status: Validated & Encrypted (AES-256)\n- Storage Region: US-East S3 Bucket\nEverything is completely up to date and compliant with your daily data retention policy.\nWarm regards,\nSarah Connor",
            ],
            // Reply 11 (Customer)
            [
                'sender_type' => 'customer',
                'user_id' => $customer->id,
                'body' => "Hi Sarah,\nThat is fantastic news. We verified the 12:45 UTC snapshot from our management dashboard.\nThe backup size and integrity check match our expectations perfectly.\nWe also tested restoring a sample table from the snapshot onto a sandbox environment.\nThe restore completed in 4 minutes with zero data loss or corruption.\nEverything looks solid across our infrastructure now.\nWe really appreciate how quickly your engineering team diagnosed and resolved the issue.\nYour transparency regarding the firewall tracking and SSL failover was top notch.\nCould you confirm if an automated post-mortem root cause analysis (RCA) report will be published?\nOur executive team usually requests RCAs for any major P1 incidents.\nThanks again for the outstanding support!\nAlexander",
            ],
            // Reply 12 (Agent)
            [
                'sender_type' => 'agent',
                'user_id' => $agent->id,
                'body' => "Hello Alexander,\nThank you for the kind words! We are glad the sandbox restore test passed smoothly.\nYes, our Reliability Engineering team publishes full Root Cause Analysis (RCA) documents for all P1/P2 incidents.\nThe RCA report for Incident #110 is currently undergoing internal review by our VP of Engineering.\nIt covers:\n1. Root cause of the optic switch hardware failure.\n2. BGP failover timing and firewall connection tracking adjustments.\n3. Long-term corrective action items (upgrading core switch modules and expanding default buffer sizes).\nWe expect the formal RCA PDF to be finalized within 24 hours.\nI will attach the PDF directly to this ticket as soon as it is published.\nPlease let me know if you need me to CC any additional team members on the RCA email.\nBest regards,\nSarah Connor",
            ],
            // Reply 13 (Customer)
            [
                'sender_type' => 'customer',
                'user_id' => $customer->id,
                'body' => "Hi Sarah,\nThat sounds great. Please CC our VP of Infrastructure on the RCA email:\nEmail: cto@enterprise.com\nAlso, please include our lead DevOps manager: devops-lead@enterprise.com.\nThey will want to review the long-term corrective action items in detail.\nSpecifically, we want to know if similar hardware switch upgrades are planned for US-West.\nWe have a secondary deployment in US-West and want to prevent similar failovers there.\nIs there a scheduled maintenance window for the US-West data center upgrades?\nIf so, please share the maintenance schedule so we can plan accordingly.\nThanks again for keeping us informed every step of the way.\nBest,\nAlexander",
            ],
            // Reply 14 (Agent)
            [
                'sender_type' => 'agent',
                'user_id' => $agent->id,
                'body' => "Hello Alexander,\nI have added cto@enterprise.com and devops-lead@enterprise.com to the RCA notification list.\nRegarding your question about US-West infrastructure upgrades:\nYes! Based on the lessons learned from this incident, we are proactively upgrading US-West hardware.\nWe have scheduled a rolling hardware maintenance window for US-West on Saturday, July 26th.\nMaintenance Window Details:\n- Start Time: Saturday, July 26, 02:00 UTC\n- End Time: Saturday, July 26, 04:00 UTC\n- Expected Impact: Zero downtime (redundant A/B rolling failover).\n- Scope: Upgrading core switch optics and doubling firewall connection tables.\nYou will receive an official email invitation with calendar links 48 hours prior.\nThank you for bringing this to our attention!\nWarm regards,\nSarah Connor",
            ],
            // Reply 15 (Customer)
            [
                'sender_type' => 'customer',
                'user_id' => $customer->id,
                'body' => "Hi Sarah,\nThank you for the detailed information regarding the US-West maintenance window.\nJuly 26th at 02:00 UTC works well for our team as it falls outside peak business hours.\nWe will notify our operations team to stand by during the maintenance window just in case.\nCould you also verify if maintenance notifications can be delivered via webhook?\nWe set up a Slack webhook integration last week under our account settings.\nIt would be super helpful to receive automated Slack alerts when maintenance starts and ends.\nCan you check if webhook alerts are enabled for scheduled maintenance events?\nIf not, please let us know how to enable them.\nThanks,\nAlexander",
            ],
            // Reply 16 (Agent)
            [
                'sender_type' => 'agent',
                'user_id' => $agent->id,
                'body' => "Hi Alexander,\nI verified your account settings and checked our webhook dispatcher service.\nYour Slack webhook integration (channel: #ops-alerts) is active and verified.\nI have enabled the 'System Maintenance & Event Status' event category on your account.\nNow, your team will receive automatic Slack messages for:\n1. 24-hour pre-maintenance reminder.\n2. Maintenance started event.\n3. Maintenance completed event.\n4. Any unexpected status state changes during maintenance.\nWe conducted a test ping payload to your webhook URL and received a 200 OK response.\nYou should have received a 'Webhook Test Successful' message in your #ops-alerts channel.\nPlease let me know if you saw that message come through!\nBest regards,\nSarah Connor",
            ],
            // Reply 17 (Customer)
            [
                'sender_type' => 'customer',
                'user_id' => $customer->id,
                'body' => "Hi Sarah,\nYes! We just confirmed that the test message arrived in #ops-alerts:\n'Webhook Test Successful - Event: System Maintenance & Event Status Enabled'.\nThe Slack formatting looks clean and easy to read.\nOur on-call team will be notified automatically during Saturday's maintenance window.\nEverything is fully configured and operational now.\nThank you again for going above and beyond to set that up for us so quickly.\nWe are extremely satisfied with the support provided on this ticket.\nI have no further questions regarding this issue.\nYou may proceed with closing this ticket once the RCA is attached.\nBest regards,\nAlexander Wright",
            ],
            // Reply 18 (Agent)
            [
                'sender_type' => 'agent',
                'user_id' => $agent->id,
                'body' => "Hello Alexander,\nFantastic! I am glad to hear that the Slack webhook test ping was successful.\nOur Reliability Engineering team has just finalized the official RCA document.\nI have attached the RCA PDF file to this ticket summary below:\n- File: RCA_Incident_110_US_East_Network_Failover.pdf\n- Summary: Optic module replacement completed; connection tracking tables increased.\n- Distribution List: CTO, Lead DevOps, Security Lead, and Systems Architect.\nCopies of this RCA have also been emailed to cto@enterprise.com and devops-lead@enterprise.com.\nWe will proceed with resolving this ticket now.\nIf you encounter any further network anomalies, please do not hesitate to reach out.\nThank you for choosing Helpdesk Enterprise Support!\nBest regards,\nSarah Connor (Lead Infrastructure Agent)",
            ],
            // Reply 19 (Customer)
            [
                'sender_type' => 'customer',
                'user_id' => $customer->id,
                'body' => "Hi Sarah,\nWe received the RCA PDF email and confirmed all attachments are readable.\nBoth our CTO and DevOps Lead reviewed the document and approved the resolution.\nThank you again to you and the entire engineering team for your stellar support.\nWe appreciate the quick response times and comprehensive troubleshooting.\nOur team will keep monitoring the dashboards as planned during Saturday's maintenance.\nEverything is complete from our end.\nHave a wonderful week ahead!\nBest regards,\nAlexander Wright\nPrincipal Systems Architect\nEnterprise Cloud Solutions",
            ],
            // Reply 20 (Agent)
            [
                'sender_type' => 'agent',
                'user_id' => $agent->id,
                'body' => "Hello Alexander,\nThank you for the warm feedback! It was a pleasure working with you and your team.\nWe are closing this ticket as Resolved now.\nShould you need assistance with anything else in the future, please feel free to open a new ticket.\nOur support team remains available 24/7 for any upcoming queries or assistance.\nHave a great week ahead as well!\nBest regards,\nSarah Connor\nHelpdesk Enterprise Support Team\nInfrastructure & NOC Operations",
            ],
        ];

        // 3. Insert all 20 replies with spaced timestamps
        $startTime = Carbon::now()->subDays(3);

        foreach ($conversations as $index => $replyData) {
            TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => $replyData['user_id'],
                'body' => $replyData['body'],
                'sender_type' => $replyData['sender_type'],
                'created_at' => (clone $startTime)->addHours($index * 3),
                'updated_at' => (clone $startTime)->addHours($index * 3),
            ]);
        }

        // Update ticket status to RESOLVED
        $ticket->update(['status' => TicketStatus::RESOLVED->value]);
    }
}
