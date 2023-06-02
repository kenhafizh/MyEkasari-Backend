<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Notification;

class MidtransController extends Controller
{
    public function callback(Request $request)
    {
        //set konfigutasi midtrans
        Config::$serverKey = config('services.midtrans.serverkey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        //buat instansce midtrans notif dgn mmanggil method notification 
        $notification = new Notification();

        //assign ke variabel untuk memudahkan coding
        $status = $notification->transaction_status;
        $type = $notification->payment_type;
        $fraud = $notification->fraud_status;
        $order_id = $notification->order_id;

        //cari transkasi berdasarkan id
        $transaction = Transaction::findOrFail($order_id);

        //handle notofikasn midtrasns
        if($status == 'capture'){
            if(type == 'credit_card')
            {
                if($fraud == 'challenge')
                {
                 $transaction->status = 'PENDING';           
                }
                else
                {
                 $transaction->status = 'SUCCESS';           
                
                }
            }
        
        }
        else if($status == 'settlement')
        {
            $transaction->status = 'SUCCESS';           

        }   
        else if($status == 'pending')
        {
            $transaction->status = 'PENDING';           

        }
        else if($status == 'deny')
        {
            $transaction->status = 'CANCELED';           

        }
        else if($status == 'expire')
        {
            $transaction->status = 'CANCELED';           

        }
        else if($status == 'cancel')
        {
            $transaction->status = 'CANCELED';           

        }

        //simpan transkasi
        $transaction = $save();
    }

    public function success()
    {
        return view('midtrans.success');
    }

    public function unfinish()
    {
        return view('midtrans.unfinish');       
    }

    public function error()
    {
        return view('midtrans.error');
    }
}   
