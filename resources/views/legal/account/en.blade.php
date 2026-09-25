@php($name = config('imprint.name'))
<h3>Your Steddle account</h3>

<p>You sign in to {{ $name }} with a Steddle account: one account for every Steddle product, kept at <a href="{{ \Steddle\Foundry\Account::server() }}">{{ parse_url(\Steddle\Foundry\Account::server(), PHP_URL_HOST) }}</a>. It holds your name, your email address, the language you chose, the public key of any passkey you register, and which Steddle products you have signed in to, with when you first and last did. There is no password.</p>

<p>When you sign in, your Steddle account tells {{ $name }} your name, email address and language, and whether you signed in with a passkey or a link by email, identified by reference and never the credential itself. {{ $name }} keeps its own copy of those details. It does not learn which other Steddle products you use.</p>

<p>In your Steddle account you can change your name or email address, and the change reaches every product you have signed in to. You can leave {{ $name }}, which deletes your account here; sign out of every product at once; or delete your Steddle account, which deletes your account in every product you have signed in to. What stays of your use of {{ $name }} after you leave is described in the rest of this policy.</p>
