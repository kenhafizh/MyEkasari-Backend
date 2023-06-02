<?php

namespace App\Http\Controllers\API;

use App\Actions\Fortify\PasswordValidationRules;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;



class UserController extends Controller
{

    use PasswordValidationRules;

    //CODINGAN LOGIN
    public function login(Request $request)
    {
        try {
            $request->validate([
                //VALIDASI INPUT
                'email' => 'email|required',
                'password' => 'required'
            ]);

            //mengecek credentials (login)
            $credentials = request(['email', 'password']);
            if(!Auth::attempt($credentials)) {
                return ResponseFormatter::error([
                    'message' => 'Unauthorized'
                ], 'Authentication Failed', 500);
            }
            
            //apakah user bener apa kagak, jika hash tidak sesuai maka beri error
            $user = User::where('email', $request->email)->first();
            if(!Hash::check($request->password, $user->password, [])) {
                throw new \Exception('Invalid Credentials'); 
            }

            //jika berhasil maka loginkan
            $tokenResult = $user->createToken('authToken')->plainTextToken; //createToken yaitu fungsibawaanlaravel
            return ResponseFormatter::success([
                'access_token' => $tokenResult, 
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Authenticated');
            
         } catch(Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went Wrong',
                'error' => $error
            ], 'Authentication Failed', 500);

         }
      }

      //DAFTAR AKUN
        public function register (Request $request)
        {
            try {
                //request untuk validasi, jika validasi selesai lanjut kepembuatan user
                $request->validate([
                    'name'=> ['required', 'string', 'max:255'],
                    'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                    'password' => $this->passwordRules()
                ]);
                
                //FIELD YANG ADA PADA DATABSE
                User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'address' => $request -> address,
                    'houseNUmber' => $request -> houseNumber,
                    'phoneNumber' => $request -> phoneNumber,
                    'city' => $request -> city,
                    'password' => Hash::make($request->password),
                ]);

                //mengambil data yang tadi telah dibuat dan disimpan
                $user = User::where('email', $request->enail)->first();
                
                //setelah register maka kemablikan token agar sekalian login
                $tokenResult = $user->createToken('authToken')->plainTextToken;

                //mengembalikan token dan data user yg telh dibuat
                return ResponseFormatter::success([
                    'access_token' => $tokenResult,
                    'token_type' => 'Bearer',
                    'user' => $user
                ]);

            //jika ada error akan dikembalikan seperti ini
            }catch (Exception $error){
                return ResponseFormatter::error([
                    'message' => 'Something went wrong',
                    'error' => $error,
                ],  'Authentication Failed', 500);

            }
        }
        
    //LOGOUT
        public function logout(Request $request)
        {
            //tiap request yang masuk lewat logout harus mengirimkan token yang sudah diberikan pada login atau register
            $token = $request->user()->currentAccessToken()->delete();

            return ResponseFormatter::success($token, 'Token Revoked');
        }

    //Pengambilan data user untuk ke API
        public function fetch(Request $request)
        {
            return ResponseFormatter::success(
                $request->user(),'Data profile user berhasil diambil');
            
        }

    //UPDATE PROFILE
        public function updateProfile(Request $request)
        {
            //ambil semua data dan masukkan kesatu variabel
            $data = $request->all();

            $user = Auth::user();
            $user->update($data);

            //mengembalikkan data yang telah diupdate
            return ResponseFormatter::success($user, 'Profile telah diperbarui');

        }
    
    //PEMBUATAN API UNTUK UPDATE PHOTO
        public function updatePhoto(Request $request)
        {
            //validasi gambar maksimal ukuran 2mb
            $validator = Validator::make($request->all(), [
                'file' => 'requiredimage\max:2048'
            ]);

        
            if($validator->fails())
            {
                return ResponseFormatter::error(
                    ['error' => $validator -> error()],
                    'Foto Gagal Diperbarui',
                    401
                );
            }

            if($request->file('file'))
            {
                $file = $request->file->store('assets/user','public');

                //Simpan foto ke database (urlnya)
        
                $user = Auth::user(); //upload foto
                $user->profile_photo_path = $file; //panggil model usernya
                $user->update(); //update field database 

                return ResponseFormatter::success([$file], 'Berhasil Upload File');
            }
        }

    
      }
    


