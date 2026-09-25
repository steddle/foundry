{{--
    The Steddle account's part of an imprint's privacy policy, in the page's
    language: `@include('foundry::legal.account')` where the policy lists
    what it collects.
--}}
@include('foundry::legal.account.'.(app()->getLocale() === 'nl' ? 'nl' : 'en'))
