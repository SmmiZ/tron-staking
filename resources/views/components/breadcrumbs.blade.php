<div class="breadcrumbs">
    <div class="breadcrumbs-title">
        {{$title}}
    </div>
    <div class="breadcrumbs-list">
        <a href="{{route('home')}}">Главная</a>
        @forelse($parents as $parent)
            <a href="{{$parent['link']}}">{{$parent['name']}}</a>
        @empty
        @endforelse
    </div>
</div>