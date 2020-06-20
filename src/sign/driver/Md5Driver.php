<?php

namespace mhs\tools\sign\driver;

class Md5Driver extends ASignDriver
{
    public function make($data)
    {
        $data = $this->format($data);
        $dataStr = $this->getDataStr($data);
        if ($this->key) {
            $dataStr .= '&key=' . $this->key;
        }

        return md5($dataStr);
    }
}