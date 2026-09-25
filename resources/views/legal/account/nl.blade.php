@php($name = config('imprint.name'))
<h3>Je Steddle-account</h3>

<p>Je logt in bij {{ $name }} met een Steddle-account: één account voor elk product van Steddle, bewaard op <a href="{{ \Steddle\Foundry\Account::server() }}">{{ parse_url(\Steddle\Foundry\Account::server(), PHP_URL_HOST) }}</a>. Daarin staan je naam, je e-mailadres, de taal die je koos, de publieke sleutel van elke passkey die je registreert, en bij welke producten van Steddle je hebt ingelogd, met wanneer je dat voor het eerst en voor het laatst deed. Er is geen wachtwoord.</p>

<p>Als je inlogt, geeft je Steddle-account {{ $name }} je naam, e-mailadres en taal door, en of je inlogde met een passkey of met een link per mail, aangeduid met een verwijzing en nooit met de inloggegevens zelf. {{ $name }} bewaart een eigen kopie van die gegevens. Welke andere producten van Steddle je gebruikt, ziet {{ $name }} niet.</p>

<p>In je Steddle-account kun je je naam of e-mailadres wijzigen, en die wijziging bereikt elk product waar je hebt ingelogd. Je kunt {{ $name }} verlaten, waarmee je account hier wordt verwijderd; in één keer uitloggen bij elk product; of je Steddle-account verwijderen, waarmee je account wordt verwijderd bij elk product waar je hebt ingelogd. Wat er na je vertrek bewaard blijft van je gebruik van {{ $name }}, staat in de rest van deze verklaring.</p>
