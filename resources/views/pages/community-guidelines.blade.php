@extends('layouts.app', ['title' => 'Community Guidelines | Snap Finder', 'description' => 'Snap Finder rules for safe, accurate, consensual, and correctly categorized profile listings.'])

@section('content')
    <section class="relative overflow-hidden pb-20 pt-24 sm:pt-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <p class="text-sm font-semibold uppercase tracking-wider text-primary">Safety</p>
            <h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-5xl">Community Guidelines</h1>
            <p class="mt-4 text-sm text-muted-foreground">Effective and last updated: July 20, 2026</p>

            <div class="mt-7 rounded-2xl border border-primary/20 bg-primary/10 p-5 leading-relaxed text-foreground sm:p-6">Snap Finder is built for intentional discovery. These Guidelines apply to accounts, listings, images, categories, tags, reports, and contact submissions—including conduct after a connection moves to Snapchat or another service.</div>

            <div class="sf-guidelines-grid mt-8 grid gap-5 lg:grid-cols-2">
                <section class="sf-rule-card"><h2>Be Honest and Get Permission</h2>
                <ul>
                    <li>List only a Snapchat account you control or have clear permission to represent.</li>
                    <li>Use an accurate display name, handle, image, bio, intent, categories, tags, and location information.</li>
                    <li>Do not impersonate another person, organization, or creator, and do not present fan or parody accounts as official.</li>
                    <li>Do not publish private information, intimate material, or images of another person without permission.</li>
                </ul></section>

                <section class="sf-rule-card sf-rule-card-critical"><h2>Keep Minors Out of Adult Content</h2>
                <p>Sexualized content involving anyone under 18 is prohibited with no exception. This includes real, fictional, computer-generated, or ambiguous content; grooming; requests for sexual material; sexual roleplay; trafficking; and language intended to suggest that a person is underage. Do not use “barely legal,” school-age, age-play, or similar framing to evade this rule. Report suspected child exploitation immediately; do not download, preserve, or redistribute the material yourself.</p></section>

                <section class="sf-rule-card"><h2>Use Adult Categories Correctly</h2>
                <ul>
                    <li>Adult Creators, After Dark, NSFW, and adult-designated tags are for users who are at least 18 and have completed the access requirements.</li>
                    <li>Mark adult listings accurately. Do not place adult offers, explicit descriptions, fetish content, or sexual solicitation in SFW categories or tags.</li>
                    <li>No non-consensual intimate content, sexual coercion, exploitation, trafficking, or requests to secretly record or expose another person.</li>
                    <li>Adult access is a screening boundary, not an identity or background check. Continue to use caution.</li>
                </ul></section>

                <section class="sf-rule-card"><h2>No Harm, Harassment, or Hate</h2>
                <p>Do not threaten, stalk, shame, blackmail, doxx, bully, sexually harass, or encourage violence or self-harm. Do not target people with hateful or dehumanizing content based on protected traits. Repeated unwanted contact and attempts to evade a block or suspension are prohibited.</p></section>

                <section class="sf-rule-card"><h2>No Scams or Dangerous Transactions</h2>
                <p>Do not use Snap Finder for phishing, account theft, fake verification, impersonation, fraudulent investment or payment schemes, money flips, stolen goods, illegal drugs, weapons, fake identification, malware, or other illegal or deceptive activity. Snap Finder does not verify purchases or provide payment protection. Never send money or sensitive credentials because a listing asks you to.</p></section>

                <section class="sf-rule-card"><h2>No Spam or Platform Abuse</h2>
                <ul>
                    <li>No bulk or repetitive listings, irrelevant tags, misleading keywords, automated submissions, scraping, or unsolicited mass outreach.</li>
                    <li>No attempts to bypass rate limits, bot checks, login, email verification, adult access, listing limits, reports, or moderation.</li>
                    <li>No malicious links, tracking intended to identify visitors, disruptive files, or attempts to test or exploit the service without written permission.</li>
                    <li>Do not file knowingly false or retaliatory reports.</li>
                </ul></section>

                <section class="sf-rule-card"><h2>Connect Safely</h2>
                <ul>
                    <li>Review the listing context before revealing or adding a handle.</li>
                    <li>Keep early conversations on a platform where you can block and report abuse.</li>
                    <li>Do not share passwords, verification codes, financial details, intimate images, or precise location information with someone you do not trust.</li>
                    <li>If you choose to meet, use a public place, tell someone you trust, arrange your own transportation, and leave if anything feels wrong.</li>
                    <li>For immediate danger, contact local emergency services. Snap Finder is not an emergency-response service.</li>
                </ul></section>

                <section class="sf-rule-card"><h2>Reporting and Enforcement</h2>
                <p>Use the report control on a listing for impersonation, incorrect adult categorization, spam, threats, prohibited content, or other safety concerns. Include useful, truthful details without reposting harmful material. You may also use the <a href="{{ route('contact') }}" class="text-primary hover:underline">contact page</a> for account, policy, or privacy concerns.</p>
                <p>Admins may recategorize, pause, reject, suspend, remove, or preserve listings; restrict or terminate accounts; and refer credible emergencies or unlawful conduct to appropriate authorities. Publication does not mean a listing has been endorsed or independently verified.</p></section>

                <section class="sf-rule-card lg:col-span-2"><h2>Appeals and Corrections</h2>
                <p>If you believe moderation affected your account or listing in error, contact us from the email associated with the account, identify the listing or decision, and explain the correction requested. We may ask for additional information. An appeal does not guarantee reversal.</p></section>
            </div>
        </div>
    </section>
@endsection
