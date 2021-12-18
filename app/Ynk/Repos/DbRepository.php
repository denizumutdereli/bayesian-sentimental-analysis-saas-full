<?php

namespace Ynk\Repos;

abstract class DbRepository {

    public function getPaginatedItems($limit = 20, $order = array('id', 'asc'), $query = null) {

        if (is_null($query) || $query == '') {
            return $this->model->orderBy($order[0], $order[1])->paginate($limit);
        }

        $data = null;
        $i = 0;
        $d = 0;
        $dump = array();
        foreach ($query as $val) {

            $val = str_replace("'", '', $val);
            $i++;
            if (preg_match("/%/", $val, $matches))
                $data .= '\'' . $val . '\' ';
            else
                $data .= $val . ' ';


            if ($i % 3 == 0) {

//                echo substr($data, 0, -1) .'<br>';

                $dump[$d++] = substr($data, 0, -1);

                $data = null;
            }
        }

        foreach ($dump as $val => $key)
            $this->model = $this->model->whereRaw($key);

        //SandBox
//            if($sandbox == 1)
//                $this->model = $this->model->orWhere('account_id','=','1');
//         dd($this->model->toSql());

        return $this->model->orderBy($order[0], $order[1])->paginate($limit);
    }

}
