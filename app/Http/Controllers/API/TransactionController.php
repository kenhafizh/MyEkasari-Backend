<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Models\Transcation;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;

class TransactionController extends Controller

{ 
 public function all(Request $request)
    {
        //Variabel yang dibutuhkan
        $id = $request->input('id');
        $limit = $request->input('limit');
        $food_id = $request->input('food_id');
        $status = $request->input('status');


    //kondisial pengambilan id
    if ($id)
    {
        $transcation = Transaction::with ('food', 'user')->find(id);

        if($transcation)
        {
            return ResponseFormatter::success(
            $transcation,
            'Data transaksi berhasil diambil'
            );

        }
        else{
            return ResponseFormatter::success(
                null,
                'Data transaksi tidak ada',
                404
            );
        }
    }

    //pengambilan selain id
    $transaction = Transaction::with(['food','user'])
                    -> where('user_id', Auth::user()->id); //mengambil data berdasarkan user yang sedang login, hanya transaksi yang dia punya bkn org lain

    if($food_id)
    {
        $transcation->where('food_id', $food_id);
    }

    if($status)
    {
        $transcation->where('status', $status);
    }

    //pengembalian data
    return ResponseFormatterr::success(
        $transaction->paginate($limit),
        'Data list transaksi berhasil diambil'
    );

    }
    
    public function update(Request $request,$id)
    {
        $transaction = Transaction::findOrFail($id);

        $transaction->update($request->all());

        return ResponseFormatter::success($transaction, 'Transaksi berhasil diperbarui');
    }

    public function checkout(Request $request)
    {
        //validasi request yang dikirim dari frontend
        $request->validate([
            'food_id' => 'required|exists:food, id',
            'user_id' => 'required|exists:users, id',
            'quantity' => 'required', 
            'total' => 'required',
            'status' => 'required',
        ]);

        //prses pembuatan database yang diinput ke database transcation
        $transcation = Transaction::create([
            'food_id' => $request->food_id,
            'user_id' => $request->user_id,
            'quantity' => $request->quantity,
            'total' => $request->total,
            'status' => $request->status,
            'payment_url' => '',
        ]);

        //Konfigurasi Midtrans
        Config::$serverKey = config('services.midtrans.serverkey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        //memanggil transksi yang tadi dibuat
        $transcation = Transaction::with(['food','user'])->find($transcation->id);

        //Membuat Transkasi Midtrans

        $midtrans = [
            'transaction_details' => [
                'order_id' => $transcation->id,
                'gross_amount' => (int) $transcation->total,
            ],
            'customer_details' => [
                'first_name' => $transcation->user->name,
                'email' => $transcation->user->email,
            ],
            'enabled_payments' => ['gopay', 'bank_transfer'],
            'vtweb' => []
        ];

        //Memanggil Midtrans
        try{
            //ambil halaman payment midtrans
            $paymentUrl = Snap::createTransaction($midtrans)->redirect_url;

            $transcation->payment_url = $paymentUrl;
            $transcation->save();

            //Mengembalikan Data ke API
            return ResponseFormatter::success($transcation,'Transaksi Berhasil');
        } 
        catch (Exception $e){
            return ResponseFormatter::error($e->getMessage(),'Transaksi Gagal');

        }

        
    }

}


