<?php

namespace App\Services;

class CommissionService
{
    public function calculateCommissionFee($data, $currencies, $all_data, $key)
    {
        $date = $data[0];
        $user_id = $data[1];
        $user_type = $data[2];
        $type = $data[3];
        $amount = $data[4];
        $currency_name = $data[5];
        $currency_data = $currencies->rates->{$currency_name};
        if($type=='deposit'){
            $fee = $amount * 0.03 / 100;
        }elseif($type=='withdraw'){
            if($user_type=='private'){
                $fee_rate = 0.3/100;
                $total_amount=0;
                $all_data = array_slice($all_data, 0, $key+1, true);
                $user_id_datas = array();
                foreach ($all_data as $item) {
                    if($item[1]==$user_id && $item[2]=='private' && $item[3]=='withdraw'){
                        $user_id_datas[] = $item;
                    }
                }
                $hold_weekid = '';
                $times = 0;
                $hold_amount = 0;
                foreach($user_id_datas as $per_data){
                    $weekid = date('oW', strtotime($per_data[0]));
                    if($hold_weekid==$weekid){
                        $times++;
                        if($hold_amount>1000){
                            $commission_amount = $per_data[4];
                        }else{
                            $hold_amount += $per_data[4] / $currencies->rates->{$per_data[5]};
                            $commission_amount = $hold_amount - 1000;
                            if ($commission_amount<0){
                                $commission_amount=0;
                            }
                        }
                    }else{
                        $times = 1;
                        $hold_amount = $per_data[4] / $currencies->rates->{$per_data[5]};
                        $commission_amount = ($hold_amount - 1000) * $currencies->rates->{$per_data[5]};
                    }
                    $hold_weekid = date('oW', strtotime($per_data[0]));
                }
                if($times > 3){
                    $fee = $amount * $fee_rate;
                }elseif($hold_amount > 1000){
                    $fee = $commission_amount * $fee_rate;
                }else{
                    $fee = 0.00;
                }
            }elseif($user_type=='business'){
                $fee_rate = 0.5/100;
                $fee = $amount * $fee_rate;
            }else{
                $fee = 'User type is undefined';
            }
        }else{
            $fee = 'Payment type is undefined';
        }
        $fee_rounded = $this->round_up($fee);
        return $fee_rounded;
    }

    public function round_up($value){
        $pow = pow ( 10, 2 );
        return number_format ( ( ceil ( $pow * $value ) + ceil ( $pow * $value - ceil ( $pow * $value ) ) ) / $pow, 2);
    }
}
