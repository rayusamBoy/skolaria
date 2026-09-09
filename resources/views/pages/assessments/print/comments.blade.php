<div>
    <table class="td-left" style="border-collapse: collapse">
        <tbody>
            <tr>
                <td><strong>CLASS TEACHER'S COMMENT:</strong></td>
                <td class="text-underline">{{ $asr->t_comment ?: str_repeat('__', 45) }}</td>
            </tr>
            <tr>
                <td><strong>PRINCIPAL'S COMMENT:</strong></td>
                <td class="text-underline">{{ $asr->p_comment ?: str_repeat('__', 45) }}</td>
            </tr>
            <tr>
                <td><strong>NEXT TERM BEGINS:</strong></td>
                <td class="text-underline">
                    {{ date('l\, jS F\, Y', strtotime($settings->firstWhere('key', 'term_begins')->value)) }}
                </td>
            </tr>
            @if ($next_term_fees = $settings->firstWhere('key', 'next_term_fees_'.strtolower($ct))->value != 0000)
                <tr>
                    <td><strong>NEXT TERM FEES:</strong></td>
                    <td>{{ Pay::getDefaultCurrencySymbol() . " " . $next_term_fees }}/-</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
