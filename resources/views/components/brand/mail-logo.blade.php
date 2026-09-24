{{-- The imprint's lockup over its mail, endorsed where the imprint is, on the mail's paper at twice the 240 by 48 it is shown at. --}}
<div style="display: flex; align-items: center; justify-content: center; width: 480px; height: 96px; background: {{ config('imprint.paper') }}; color: {{ config('imprint.ink') }};">
    <foundry:lockup :endorsed="config('imprint.endorsed', true)" style="height: 72px; max-width: 440px;" />
</div>
