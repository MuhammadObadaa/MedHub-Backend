<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\Medicine;
use App\http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\File;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    //TODO: make show() call show(User) route instead of reimplement it
    public function show()
    {
        $lang = $this->lang();
        $message['ar'] = 'تم عرض الصفحة الشخصة بنجاح';
        $message['en'] = 'your information displayed successfully!';
        $message = ['message' => $message[$lang]];
        return (new UserResource(AuthMiddleware::getUser()))->additional($message);
    }

    public function showUser(User $user)
    {
        $lang = $this->lang();
        $message['ar'] = 'تم عرض الصفحة الشخصة بنجاح';
        $message['en'] = 'your information displayed successfully!';
        $message = ['message' => $message[$lang]];
        return (new UserResource($user))->additional($message);
    }

    public function list()
    {
        $lang = $this->lang();
        $users = User::where('is_admin', FALSE)->get();

        $message['ar'] = 'تم عرض الصفحات الشخصة بنجاح';
        $message['en'] = 'Users information displayed successfully!';
        $message = ['message' => $message[$lang]];
        return UserResource::collection($users)->additional($message);
    }

    public function favor(Medicine $medicine)
    {
        $lang = $this->lang();
        $user = AuthMiddleware::getUser();

        // you can send medicine->id and the medicine object itself
        if (!$user->hasFavored($medicine)) {
            $user->favors()->attach($medicine->id);

            $medicine->update(['popularity' =>  $medicine->popularity + 1]);
        }

        $message['ar'] = 'تمت الإضافة للمفضلة بنجاح';
        $message['en'] = 'medicine added to favorites successfully';

        return response()->json(['message' => $message[$lang]]);
    }

    public function unFavor(Medicine $medicine)
    {
        $lang = $this->lang();
        $user = AuthMiddleware::getUser();

        if ($user->hasFavored($medicine)) {
            $user->favors()->detach($medicine->id);

            $medicine->update(['popularity' =>  $medicine->popularity - 1]);
        }

        $message['ar'] = 'تمت الإزالة من المفضلة بنجاح';
        $message['en'] = 'medicine removed from favorites successfully';

        return response()->json(['message' => $message[$lang]]);
    }

    //TODO: specify this method as needed. at least some of them
    public function update()
    {
        $lang = $this->lang();
        $user = AuthMiddleWare::getUser();

        if (request()->has('name'))
            $user->update(['name' => request('name')]);
        if (request()->has('pharmacyName'))
            $user->update(['pharmacyName' => request('pharmacyName')]);
        if (request()->has('pharmacyLocation'))
            $user->update(['pharmacyLocation' => request('pharmacyLocation')]);
        if (request()->has('newPassword')) {
            if (Hash::check(request('oldPassword'), $user->password))
                //return response()->json(['message' => 'Wrong Password!'], 400);
                $user->update(['password', Hash::make('newPassword')]);
        }
        if (request()->has('image')) {
            $validatedImage = Validator::make(request()->all(), ['image' => 'image']);

            if (!$validatedImage->fails()) {
                //$message['ar'] = 'نوع الصورة غير صحيح';
                //$message['en'] = 'Invalid image file';
                //return response()->json(['message' => $message[$lang]], 400);

                $imageFile = request()->file('image')->store('app', 'public');
                // if (File::exists($user->image))
                //     File::delete($user->image);
                if ($user->image != null)
                    Storage::disk('public')->delete($user->image);
                $user->update(['image' => $imageFile]);
            }
        }

        $message['ar'] = 'تم التعديل بنجاح';
        $message['en'] = 'Changes applied successfully!';

        return response()->json(['message' => $message[$lang]]);
    }
}
