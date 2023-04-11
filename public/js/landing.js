let copyBtn = document.querySelector('.copy-btn');

copyBtn.addEventListener('click', function () {
    let copyText = document.querySelector('.subtitle').innerText;
    let el = document.createElement('textarea');

    el.value = copyText;
    el.setAttribute('readonly', '');
    el.style.position = 'absolute';
    el.style.left = '-9999px';

    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
});