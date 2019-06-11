<?php
function verify_fzzform($dataArray)
{
    // captcha verify
    if (empty($dataArray['yzm'])) {
        return -44;
    }

    if (empty($dataArray['token'])) {
        return -45;
    }

    // 非空检验
    if (empty($dataArray['username'])) {
        return -3;
    }

    // 验证姓名长度，检验是否是纯中文或者纯英文
    if (!check_username($dataArray['username'])) {
        return -5;
    }

    // 电话号码验证
    if (!empty ($dataArray['tel'])) {
        if (strlen($dataArray['tel']) > 20 || strlen($dataArray['tel'] < 5)) {
            return -4;
        }
    }

    // 身份證前四位簡單檢測
    if (!empty ($dataArray['hkcode'])) {
        if (strlen($dataArray['hkcode']) !== 4) {
            return -4;
        }
    }

    return 0;
}

/**
 * 检查姓名字符串是否全是中文或者全是英文
 * @param string $username_str
 * @return boolean
 */
function check_username($username_str)
{
    $username_str = trim($username_str);
    if (!empty($username_str) && strlen($username_str) > 3 && strlen($username_str) < 24) {
        return true;
    } else {
        return false;
    }
}

function post_check($content, $allowed_fields = array())
{
    if (!get_magic_quotes_gpc()) {
        if (is_array($content)) {
            foreach ($content as $key => $value) {
                if (!empty($allowed_fields) && !in_array($key, $allowed_fields)) {
                    unset($content[$key]);
                } else {
                    $content[$key] = addslashes(strip_tags(trim($value)));
                }
            }
        } else {
            addslashes($content);
            $content = strip_tags($content);
        }
    }
    return $content;
}