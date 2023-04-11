<div>
    <div class="user-panel">
        <div class="user-panel-logo">
            <a href="{{route('home')}}"><img src="{{asset('img/black-icon.svg')}}" alt="Tron Energy"></a>
        </div>
        <div class="user-panel-button">
            <a href="" class="logout"
               onclick="event.preventDefault();document.getElementById('logout-form').submit();"></a>
            <form id="logout-form" action="{{route('logout')}}" method="POST" style="display: none;">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
            </form>
        </div>
    </div>
    <div class="navigation">
        <div class="navigation-title">Меню</div>
        <div class="navigation-menu">
            <nav>
                @forelse($menu as $menuItem)
                    <a href="{{$menuItem['href']}}" class="navigation-menu-item {{$menuItem['active']}}"><span
                                class="mdi {{$menuItem['icon']}}"></span>{{$menuItem['text']}}</a>
                @empty
                @endforelse
            </nav>
        </div>
    </div>

</div>
