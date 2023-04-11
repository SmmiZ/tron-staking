document.addEventListener('DOMContentLoaded', function(){
    const _menuBtn = document.getElementById('menu');
    const _closeMenuBtn = document.getElementById('close-menu');

    _menuBtn.addEventListener('click', function(){
        document.querySelectorAll('.navigation-wrap')[0].classList.toggle('opened');
    });
    _closeMenuBtn.addEventListener('click', function(){
        document.querySelectorAll('.navigation-wrap')[0].classList.remove('opened');
    });

    bindTabs();
});


function bindTabs() {
    let tabBtns = Array.from(document.querySelectorAll('.tab-btn'));
    let tabs = Array.from(document.querySelectorAll('.tab'));

    const tabClick = (e) => {
        e.preventDefault();
        tabs.forEach(node => {
            node.classList.remove('tab-visible');
        });
        tabBtns.forEach(node => {
            node.classList.remove('active');
        });
        let __secondName = e.currentTarget.getAttribute('data-type');
        document.getElementById('tab-'+__secondName).classList.add('tab-visible');
        e.currentTarget.classList.add('active');

    }

    tabBtns.forEach(node => {
        node.addEventListener('click', tabClick)
    });
}

document.addEventListener('touchstart', handleTouchStart, false);
document.addEventListener('touchmove', handleTouchMove, false);

var xDown = null;
var yDown = null;

function handleTouchStart(evt) {
    xDown = evt.touches[0].clientX;
    yDown = evt.touches[0].clientY;
};

function handleTouchMove(evt) {
    if ( ! xDown || ! yDown ) {
        return;
    }

    var xUp = evt.touches[0].clientX;
    var yUp = evt.touches[0].clientY;

    var xDiff = xDown - xUp;
    var yDiff = yDown - yUp;

    if ( Math.abs( xDiff ) > Math.abs( yDiff ) ) {

        if ( xDiff > 0 ) {
            document.querySelectorAll('.navigation-wrap')[0].classList.remove('opened');
        }
    }
    xDown = null;
    yDown = null;
};

function show_message(type = 'error',title = 'Ошибка', message = 'Что-то пошло не так, попробуйте позже'){

    let popup  =
        `<div class="`+type+` popup message__popup active" onclick="this.remove()">
        <div class="popup__flex">
            <div class="popup__wrap">
                <button type="button" class="popup__close"></button>
                <div class="popup__icon `+type+`"></div>
                <div class="popup__title"><h3>`+title+`</h3></div>
                <div class="popup__text"><p>`+message+`</p></div>
            </div>
        </div>
    </div>`;
    let div = document.createElement("div");
    div.innerHTML = popup;
    document.body.appendChild(div);
    fadeIn(div);
}
function fadeIn(el) {
    el.style.opacity = 0;
    (function fade() {
        var val = parseFloat(el.style.opacity);
        if (!((val += .1) > 1)) {
            el.style.opacity = val;
            requestAnimationFrame(fade);
        }
    })();
};

const StopLoop = new Error("StopLoop");
function submitWithConfimation(_formID){
    let popup  =
        `<div class="attention_popup popup active">
        <div class="popup__flex">
            <div class="popup__wrap">
                <button type="button" class="popup__close" onclick="this.closest('.popup').remove()"></button>
                <div class="popup__icon attention"></div>
                <div class="popup__title"><h3>Внимание!</h3></div>
                <div class="popup__text"><p>Данное действие отразиться на работе сервиса. Необходимо подтверждение</p></div>
                <div class="pin-inputs">
                    <input type="tel" class="pin-item">
                    <input type="tel" class="pin-item">
                    <input type="tel" class="pin-item">
                    <input type="tel" class="pin-item">
                    <input type="tel" class="pin-item">
                    <input type="tel" class="pin-item">
                </div>
                <div class="text-center">
                    <button class="btn" onclick="confirmPinPopup('`+_formID+`')">Подтвердить</button>
                </div>
            </div>
        </div>
    </div>`;
    let div = document.createElement("div");
    div.innerHTML = popup;
    document.body.appendChild(div);
    fadeIn(div);
    bindPinAction();
}

function bindPinAction()
{
    var pins = document.querySelectorAll('.pin-item');

    pins.forEach( _pin => {
        _pin.addEventListener('input', (e) => {
            if(_pin.value.length > 1) _pin.value = _pin.value.slice(0, 1)
            nextEmptyPin(pins);
        });

        _pin.addEventListener('keydown', (e) => {
            e = e || window.event;
            let key = e.which || e.keyCode;
            let ctrl = e.ctrlKey ? e.ctrlKey : ((key === 17) ? true : false);
            if ( key == 86 && ctrl ) {
                navigator.clipboard.readText()
                    .then(text => {
                        textArray = text.split('');
                        pins.forEach( _pin => {
                            _pin.value = textArray[[].indexOf.call(_pin.parentElement.children, _pin)];
                        });
                        nextEmptyPin(pins);
                    })
                    .catch(err => {
                        console.error('что-то пошло не так ', err);
                    });
            }
        }, false);
    });

}

function confirmPinPopup(_formID)
{
    resultPin = '';
    document.querySelectorAll('.pin-item').forEach( _pin => {
        resultPin += _pin.value;
    });
    document.querySelectorAll('.pin-confirmation').forEach( _pinField => {
        _pinField.value = resultPin;
    });
    document.querySelectorAll('.popup').forEach( _pop => {
        _pop.remove();
    });
    document.getElementById(_formID).submit();
}

function nextEmptyPin(pins)
{
    try {
        pins.forEach( _pin => {
            if(_pin.value == ''){
                _pin.focus();
                throw StopLoop;
            }
        });
    } catch(error) { if(error != StopLoop) throw error; }

}
