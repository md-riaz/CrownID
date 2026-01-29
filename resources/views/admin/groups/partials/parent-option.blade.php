@php
    $indent = str_repeat('&nbsp;&nbsp;&nbsp;', $level);
    $prefix = $level > 0 ? '└─ ' : '';
    $isSelected = $selected == $group->id ? 'selected' : '';
@endphp

<option value="{{ $group->id }}" {{ $isSelected }}>
    {!! $indent !!}{{ $prefix }}{{ $group->name }}
</option>

@if(isset($group->children) && count($group->children) > 0)
    @foreach($group->children as $child)
        @include('admin.groups.partials.parent-option', ['group' => $child, 'level' => $level + 1, 'selected' => $selected])
    @endforeach
@endif
