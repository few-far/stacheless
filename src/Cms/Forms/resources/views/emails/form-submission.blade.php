@component('mail::message')
# Form Submission: {{ $form['title'] }}

The "{{ $form['title'] }}" form was just submitted.

## Submission
<dl>
@foreach ($field_rows as $row)
    <dt>{{ $row['label'] }} ({{ $row['name'] }})</dt>
    <dd>{{ $row['value'] }}</dd>
@endforeach
</dl>

## Details
<dl>
@foreach ($meta_rows as $row)
    <dt>{{ $row['name'] }}</dt>
    <dd>{{ $row['value'] }}</dd>
@endforeach
</dl>

{{-- @component('mail::button', ['url' => $order_url])
View your Order
@endcomponent --}}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
