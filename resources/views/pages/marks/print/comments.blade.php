<div>
    <table class="td-left" style="border-collapse: collapse;">
        <tbody>
            <tr>
                <td><strong>CLASS TEACHER'S COMMENT:</strong></td>
                <td> {{ $exr->t_comment ?: str_repeat('__', 50) }}</td>
            </tr>
            <tr>
                <td><strong>PRINCIPAL'S COMMENT:</strong></td>
                <td> {{ $exr->p_comment ?: str_repeat('__', 50) }}</td>
            </tr>
            <tr>
                <td><strong>NEXT TERM BEGINS:</strong></td>
                <td>{{ date('l\, jS F\, Y', strtotime($settings->firstWhere('type', 'term_begins')->description)) }}</td>
            </tr>
            <tr>
                <td><strong>NEXT TERM FEES:</strong></td>
                <td>{{ Pay::getCurrencyUnit() . " " . $settings->firstWhere('type', 'next_term_fees_' . strtolower($ct))->description }}/-</td>
            </tr>
        </tbody>
    </table>
</div>
