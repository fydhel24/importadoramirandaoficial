@php
    $semanas = App\Models\Semana::all();
@endphp

<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
    @foreach ($semanas as $semana)
        <li class="nav-item">
            <a href="{{ url('semanas/' . $semana->id) }}" class="nav-link">
                <i class="nav-icon fas fa-calendar-week"></i>
                <p>{{ $semana->nombre }}</p>
            </a>
        </li>
    @endforeach
</ul>
