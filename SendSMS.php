<?php

# Copyright 2013 CardBoardFish
# http://www.cardboardfish.com/
# See readme.txt for terms of use. 

$errstr = "";
$errcode = 0;

class SMS {
    var $dest_addr;
    var $source_addr;
    var $source_addr_ton;
    var $message;
    var $data_coding_scheme;
    var $delivery_receipt;
    var $user_data_header;
    var $user_reference;
    var $validity_period;
    var $delay_until;
    var $local_time;
    var $retry;

    function SMS($da = "", $sa = "", $msg = "", $sat = "", $dcs = "", $dr = "", $udh = "", $ur = "", $vp = "", $du = "", $lt = "") {
        global $errstr;
        $errstr = "Construction failed.";

        if (!$this->setDA($da)) return false;
        $errstr = "DA, no error";
        if (!$this->setSA($sa)) return false;
        $errstr = "SA, no error";
        if (!$this->setMSG($msg)) return false;
        $errstr = "MSG, no error";
        if (!$this->setST($sat)) return false;
        $errstr = "ST, no error";
        if (!$this->setDC($dcs)) return false;
        $errstr = "DC, no error";
        if (!$this->setDR($dr)) return false;
        $errstr = "DR, no error";
        if (!$this->setUD($udh)) return false;
        $errstr = "UD, no error";
        if (!$this->setUR($ur)) return false;
        $errstr = "UR, no error";
        if (!$this->setVP($vp)) return false;
        $errstr = "VP, no error";
        if (!$this->setDU($du)) return false;
        $errstr = "DU, no error";
        if (!$this->setLT($lt)) return false;
        $errstr = "";

        $this->retry = true;
    }

    function setDA ($da) {
        global $errstr;

        if ($da == "") {
            $this->dest_addr = "";
            return true;
        }
        $das = explode(",", $da);

        $dests = array();

        foreach ($das as $dest) {
            preg_match("/(\+|00)?([1-9]\d{7,15})/", $dest, $matches);
            if ($matches[2] != "") {
                array_push ($dests, $matches[2]);
            } else {
                $this->dest_addr = "";
                $errstr = "Destination not recognised.";
                return false;
            }
        }

        $this->dest_addr = implode(",",$dests);

        return true;
    }

    function setSA ($sa) {
        global $errstr;

        if ($sa == "") {
            $this->source_addr = "";
            return true;
        }

        preg_match("/^(\d{1,16}|.{1,11})$/", $sa, $matches);
        if ($matches[1] != "") {
            $this->source_addr = urlencode($sa);
            return true;
        } else {
            $errstr = "Source address not recognised.";
            return false;
        }
    }

    function setMSG ($msg) {
        $this->message = $msg;
        return true;
    }

    function setST ($st) {
        global $errstr;

        if ($st == "") {
            $this->source_addr_ton = "";
            return true;
        } else {
            preg_match("/^[105]$/", $st, $matches);
            if ($matches[0] != "") {
                $this->source_addr_ton = $st;
                return true;
            } else {
                $errstr = "Source type of number must be 1, 0 or 5.";
                return false;
            }
        }
    }

    function setDC ($dcs) {
        global $errstr;

        $dcs = "" . $dcs;

        if ($dcs == "") {
            $this->dcs = "";
            return true;
        } else {
            preg_match("/^[0124567]$/", $dcs, $matches);
            if ($matches[0] != "") {
                $this->data_coding_scheme = $dcs;
                return true;
            } else {
                $errstr = "Data coding scheme must be one of:\n\t0 - Flash\n\t1 - Normal (default)\n\t2 - Binary\n\t4 - UCS2\n\t5 - Flash UCS2\n\t6 - Flash GSM\n\t7 - Normal GSM\n";
                return false;
            }
        }
    }

    function setDR ($dr) {
        global $errstr;

        if ($dr == "") {
            $this->dr = "";
            return true;
        } else {
            preg_match("/^[012]$/", $dr, $matches);
            if ($matches[0] != "") {
                $this->delivery_receipt = $dr;
                return true;
            } else {
                $errstr = "Delivery receipt request must be 0, 1 or 2.";
                return false;
            }
        }
    }

    function setUD ($udh) {
        global $errstr;

        if ($udh == "") {
            $this->user_data_header = "";
            return true;
        } else {
            preg_match("/^[0-9a-fA-F]{1,17}$/", $udh, $matches);
            if ($matches[0] != "") {
                $this->user_data_header = $udh;
                return true;
            } else {
                $errstr = "User header data invalid.";
                return false;
            }
        }
    }

    function setUR ($ur) {
        global $errstr;

        if ($ur == "") {
            $this->user_reference = "";
            return true;
        } else {
            preg_match("/^\w{1,16}$/", $ur, $matches);
            if ($matches[0] != "") {
                $this->user_reference = $ur;
                return true;
            } else {
                $errstr = "User reference invalid. Must be 1-16 chars: " . $ur;
                return false;
            }
        }
    }

    function setVP ($vp) {
        global $errstr;

        if ($vp == "") {
            $this->validity_period = "";
            return true;
        } else {
            preg_match("/^\d+$/", $vp, $matches);
            if ($matches[0] != "" && $matches[0] > 0 && $matches[0] <= 10080) {
                $this->validity_period = $vp;
                return true;
            } else {
                $errstr = "Validity period must be a number between 0 and 10080.";
                return false;
            }
        }
    }

    function setDU ($du) {
        global $errstr;

        if ($du == "") {
            $this->delay_until = "";
            return true;
        } else {
            preg_match("/^\d{10}$/", $du, $matches);
            if ($matches[0] != "") {
                $this->delay_until = $du;
                $this->setLT("");
                return true;
            } else {
                $errstr = "Delay Until must be a 10 digit UCS timestamp.";
                return false;
            }
        }
    }

    function setLT ($lt) {
        global $errstr;

        if ($lt == "") {
            if ($this->delay_until != "") {
                $this->local_time = time();
                return true;
            } else {
                $this->local_time = "";
                return true;
            }
        } else {
            preg_match("/^\d{10}$/", $lt, $matches);
            if ($matches[0] != "") {
                $this->local_time = $lt;
                return true;
            } else {
                $errstr = "Local Time must be a 10 digit UCS timestamp.";
                return false;
            }
        }
    }

}

function GSMEncode ($to_encode) {

    $gsmchar = array (
        "\x0A" => "\x0A",
        "\x0D" => "\x0D",

        "\x24" => "\x02",

        "\x40" => "\x00",

        "\x13" => "\x13",
        "\x10" => "\x10",
        "\x19" => "\x19",
        "\x14" => "\x14",
        "\x1A" => "\x1A",
        "\x16" => "\x16",
        "\x18" => "\x18",
        "\x12" => "\x12",
        "\x17" => "\x17",
        "\x15" => "\x15",

        "\x5B" => "\x1B\x3C",
        "\x5C" => "\x1B\x2F",
        "\x5D" => "\x1B\x3E",
        "\x5E" => "\x1B\x14",
        "\x5F" => "\x11",

        "\x7B" => "\x1B\x28",
        "\x7C" => "\x1B\x40",
        "\x7D" => "\x1B\x29",
        "\x7E" => "\x1B\x3D",
        
        "\x80" => "\x1B\x65",

        "\xA1" => "\x40",
        "\xA3" => "\x01",
        "\xA4" => "\x1B\x65",
        "\xA5" => "\x03",
        "\xA7" => "\x5F",

        "\xBF" => "\x60",

        "\xC0" => "\x41",
        "\xC1" => "\x41",
        "\xC2" => "\x41",
        "\xC3" => "\x41",
        "\xC4" => "\x5B",
        "\xC5" => "\x0E",
        "\xC6" => "\x1C",
        "\xC7" => "\x09",
        "\xC8" => "\x45",
        "\xC9" => "\x1F",
        "\xCA" => "\x45",
        "\xCB" => "\x45",
        "\xCC" => "\x49",
        "\xCD" => "\x49",
        "\xCE" => "\x49",
        "\xCF" => "\x49",

        "\xD0" => "\x44",
        "\xD1" => "\x5D",
        "\xD2" => "\x4F",
        "\xD3" => "\x4F",
        "\xD4" => "\x4F",
        "\xD5" => "\x4F",
        "\xD6" => "\x5C",
        "\xD8" => "\x0B",
        "\xD9" => "\x55",
        "\xDA" => "\x55",
        "\xDB" => "\x55",
        "\xDC" => "\x5E",
        "\xDD" => "\x59",
        "\xDF" => "\x1E",

        "\xE0" => "\x7F",
        "\xE1" => "\x61",
        "\xE2" => "\x61",
        "\xE3" => "\x61",
        "\xE4" => "\x7B",
        "\xE5" => "\x0F",
        "\xE6" => "\x1D",
        "\xE7" => "\x63",
        "\xE8" => "\x04",
        "\xE9" => "\x05",
        "\xEA" => "\x65",
        "\xEB" => "\x65",
        "\xEC" => "\x07",
        "\xED" => "\x69",
        "\xEE" => "\x69",
        "\xEF" => "\x69",

        "\xF0" => "\x64",
        "\xF1" => "\x7D",
        "\xF2" => "\x08",
        "\xF3" => "\x6F",
        "\xF4" => "\x6F",
        "\xF5" => "\x6F",
        "\xF6" => "\x7C",
        "\xF8" => "\x0C",
        "\xF9" => "\x06",
        "\xFA" => "\x75",
        "\xFB" => "\x75",
        "\xFC" => "\x7E",
        "\xFD" => "\x79" 

    );

    # using the NO_EMPTY flag eliminates the need for the shift pop correction
    $chars = preg_split("//", $to_encode, -1, PREG_SPLIT_NO_EMPTY);

    $to_return = "";

    foreach ($chars as $char) {
        preg_match("/[A-Za-z0-9!\/#%&\"=\-'<>\?\(\)\*\+\,\.;:]/", $char, $matches);
        if (isset($matches[0])) {
            $to_return .= $char;
        } else {
            if (!isset($gsmchar[$char])) {
                $to_return .= "\x20";
            } else {
                $to_return .= $gsmchar[$char];
            }
        }
    }
    return $to_return;
}

function send_sms_full ($destination, $source, $message, $source_addr_ton = "", $dcs = "", $dr = "", $udh = "", $user_reference = "", $validity_period = "", $delay_until = "", $local_time = "") {
    global $errstr, $errcode;

    $das = explode(",", $destination);

    $dests = count($das);

    $batches = array_chunk($das, 10);

    $replies = array();

    foreach ($batches as $batch) {

        $batchda = implode(",", $batch);

        $sms = new SMS($batchda, $source, $message, $source_addr_ton, $dcs, $dr, $udh, $user_reference, $validity_period, $delay_until, $local_time);
        $batchreplies = send_sms_object($sms);
        if (!$batchreplies) {
            if ($errcode == -15) {
                $errcode = 0;
                $batchreplies = array();
                for ($i = 0; $i < count($batch); $i++) {
                    array_push ($batchreplies, "-15");
                }
            } else {
                return false;
            }
        }
        $valfreq = array_count_values($batchreplies);
        if (isset($valfreq['-20']) && $valfreq['-20'] > 0) {
            $retrybatch = array();
            for ($i = 0; $i < count($batch); $i++) {
                if ($batchreplies[$i] == '-20') {
                    $retrybatch[] = $batch[$i];
                }
            }
            $rb = implode(",", $retrybatch);
            $sms->setDA($rb);
            $retryreplies = send_sms_object($sms);
            for ($i = 0; $i < count($batch); $i++) {
                if ($batchreplies[$i] == '-20') {
                    $batchreplies[$i] = array_shift($retryreplies);
                }
            }
        }
        $replies = array_merge($replies, $batchreplies);
    }

    return $replies;

}

function send_sms ($destination, $source, $message) {
    return send_sms_full ($destination, $source, $message, "","","","","","","","");
}

function includeif ($existing, $prefix) {
    if ($existing == "") {
        return "";
    } else {
        return $prefix . $existing;
    }
}

$systemtype = "H";

function send_sms_object ($sms) {
    global $sms_username, $sms_password, $systemtype, $errstr, $errcode;

    $username = urlencode($sms_username);
    $password = urlencode($sms_password);

    $dcs = $sms->data_coding_scheme;
    if ($dcs == "" || $dcs == 1) {
        $dcs = 6;
        $msg = urlencode(GSMEncode($sms->message));
    } else if ($dcs == 0) {
        $dcs = 7;
        $msg = urlencode(GSMEncode($sms->message));
    } else {
        $msg = urlencode($sms->message);
    }

    $request = "http://sms1.cardboardfish.com:9001/HTTPSMS?S={$systemtype}&UN=${username}&P=${password}&DA={$sms->dest_addr}&SA={$sms->source_addr}&M=${msg}";
    if (!$sms->source_addr_ton) {
        preg_match("/\w/", $sms->source_addr, $matches);
        if ($matches) {
            $sms->setST("5");
        }
    }
    # echo "$request\n";
    $request .= includeif ($sms->source_addr_ton, "&ST=");
    $request .= includeif ($dcs, "&DC=");
    $request .= includeif ($sms->delivery_receipt, "&DR=");
    $request .= includeif ($sms->user_reference, "&UR=");
    $request .= includeif ($sms->user_data_header, "&UD=");
    $request .= includeif ($sms->validity_period, "&VP=");
    $request .= includeif ($sms->delay_until, "&DU=");
    $request .= includeif ($sms->local_time, "&LT=");
    # echo "$request\n";
    $ch = curl_init($request);

    if (!$ch) {
        $errstr = "Could not connect to server.";
        return false;
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $serverresponse = curl_exec($ch);

    if (!$serverresponse) {
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errstr = "HTTP error: $code\n";
        return false;
    }

    preg_match("/(OK.*)\r$/", $serverresponse, $matches);

    if (!isset($matches[0])) {
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($code == 400) {
            $errstr = "(Server) Bad request.";
        } else if ($code == 401) {
            $errstr = "(Server) Invalid username / password.";
        } else if ($code == 402) {
            $errstr = "(Server) Credit too low, payment required.";
        } else if ($code == 503) {
            $errcode = -15;
            $errstr = "(Server) Destination not recognised.";
        } else if ($code == 500) {
            if ($sms->retry) {
                $sms->retry = false;
                $r = sendSMS($sms);
                if (!$r) {
                    $errstr = "(Server) Error, retry failed.";
                } else {
                    return $r;
                }
            } else {
                $errstr = "(Server) Error, retry failed.";
            }
        }
        return false;
    }

    $response = $matches[1];

    preg_match("/^OK((\s\-?\d+)+)(\sUR:.+)?/", $response, $matches);

    $number = explode(" ", $matches[1]);

    # Drop the dead entry
    array_shift($number);

    $to_return = array();
    foreach ($number as $id) {
        $to_return[] = $id;
    }

    return $to_return;
}

?>
