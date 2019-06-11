$(function () {
    var yzmimg = document.getElementById('yzm_img');
    yzmimg.onclick = updateyzm;

    function updateyzm() {
        yzmimg.src = 'captcha.php?v=' + Math.random();
    }

    var form1 = document.getElementById('form1');
    form1.removeAttribute('onsubmit');
    form1.action = 'javascript:void(0)';
    var sub = document.getElementById('submit-btn');
    sub.onclick = function (e) {
        e.preventDefault();
        var username = form1.querySelector('[name=username]').value,
            hkid = form1.querySelector('[name=hkid]').value,
            yzm = form1.querySelector('[name=yzm]').value;
        if (username == '') {
            dialog('請輸入姓名！');
        }
        if (hkid == '') {
            dialog('請輸入身份證號前四位！');
        }
        if (yzm == "") {
            dialog("請輸入驗證碼！");
            return;
        }

        grecaptcha.ready(function () {
            grecaptcha.execute('6LfP9acUAAAAADkx49DJRe4In6EEpnI31tt0DTXr', {action: 'submit_signature'}).then(function (token) {
                $('#form1').prepend('<input type="hidden" name="g-recaptcha-response" value="' + token + '">');

                submitHandle({
                    'username': username,
                    'hkcode': hkid,
                    'tel': form1.querySelector('[name=tel]').value,
                    'yzm': yzm,
                    'token': token,
                    'sector': form1.querySelector('[name=sector]').value
                });
            });
        });
    };

    function submitHandle(o) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'submit_demo.php', true);
        xhr.addEventListener('load', success, false);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.send('&username=' + o.username + '&hkcode=' + o.hkcode + '&yzm=' + o.yzm + '&sector=' + o.sector + '&tel=' + o.tel + '&token=' + o.token);
    }

    function success(e) {
        this.removeEventListener('load', success, false);
        try {
            var data = JSON.parse(e.target.responseText);
        } catch (err) {
            dialog('發生未知錯誤，請稍後再試！');
        }

        switch (data.errorcode) {
            case 0:
                dialog(data.message, function () {
                    location.href = 'sign.html';
                });
                break;
            case -43:
                dialog(data.message, function () {
                    updateyzm();
                    document.querySelector('[name=yzm]').focus();
                });
                break;
            default:
                dialog(data.message, updateyzm);
        }
    }

    function dialog(msg, fn) {
        var div = document.createElement('div'),
            styleEl = document.createElement('style'),
            closebtn = null,
            okbtn = null;
        styleEl.id = 'dialog-style';

        styleEl.innerText =
            '.dialog-box{' +
            'position: fixed;width: 80%; min-height: 120px;' +
            'max-width: 360px; left: 0;right: 0;top: 38%;margin-left: auto;margin-right: auto;' +
            'background: #fff;border-radius: 6px; box-shadow: #aaa 1px 1px 5px 3px;' +
            'padding: 10px;box-sizing: border-box;' +
            '}' +
            '.dialog-header{' +
            'border-bottom: 1px solid #ddd; text-align: right;' +
            '}' +
            '#dialog-btn-close{' +
            'font-size: 24px; color: #a00; display: inline-block; width: 25px; height: 25px;' +
            'text-align: center;line-height: 25px;cursor: pointer;' +
            '}' +
            '.dialog-body{' +
            'font-size: 16px; color: #333; padding: 10px 0;' +
            '}' +
            '.dialog-footer{' +
            'text-align: center;' +
            '}' +
            '#dialog-btn-ok{' +
            'display: inline-block;padding: 5px 10px; border-radius: 4px; border: 1px solid #ddd;' +
            'font-size: 16px;line-height: 1em;' +
            '}' +
            '#dialog-btn-ok:hover{' +
            'background: #888; color: #fff;' +
            '}';

        div.id = 'dialog-box';
        div.className = 'dialog-box';
        div.innerHTML =
            '<div class="dialog-header"><b id="dialog-btn-close">&times;</b></div>' +
            '<div class="dialog-body">' + (msg || '') + '</div>' +
            '<div class="dialog-footer"><span id="dialog-btn-ok">確定</span></div>';
        if (!document.getElementById('dialog-style')) {
            document.head.appendChild(styleEl);
        }
        if (!document.getElementById('dialog-box')) {
            document.body.appendChild(div);
        }
        closebtn = document.getElementById('dialog-btn-close');
        okbtn = document.getElementById('dialog-btn-ok');
        closebtn.addEventListener('click', function closeFn() {
            closebtn.removeEventListener('click', closeFn, false);
            if (div && div.parentNode) {
                div.parentNode.removeChild(div);
            }

            if (fn) fn();
        }, false);

        okbtn.addEventListener('click', function okFn() {
            okbtn.removeEventListener('click', okFn, false);
            if (div && div.parentNode) {
                div.parentNode.removeChild(div);
            }

            if (fn) fn();
        }, false);
    }
});