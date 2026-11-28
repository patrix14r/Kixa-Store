{{-- Logo SVG de Visa o Mastercard --}}
@props(['marca' => 'visa'])

<svg viewBox="0 0 48 32" role="img" aria-label="{{ $marca === 'mastercard' ? 'Mastercard' : 'Visa' }}" {{ $attributes->class(['h-6 w-9 shrink-0']) }}>
    <rect x="0.5" y="0.5" width="47" height="31" rx="4.5" fill="#fff" stroke="#d9d9de"/>
    @if ($marca === 'mastercard')
        <circle cx="19" cy="16" r="8" fill="#eb001b"/>
        <circle cx="29" cy="16" r="8" fill="#f79e1b"/>
        <path d="M24 9.76a8 8 0 0 1 0 12.48 8 8 0 0 1 0-12.48z" fill="#ff5f00"/>
    @else
        <text x="24" y="20.5" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="13" font-weight="800" font-style="italic" letter-spacing="0.4" fill="#1a1f71">VISA</text>
    @endif
</svg>
