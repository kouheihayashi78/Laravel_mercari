<?php

namespace App\Http\Controllers\MyPage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Mypage\Profile\EditRequest;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Image;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function showProfileEdit()
    {
        $user = Auth::user();
        return view('mypage.profile_edit_form', compact('user', $user));
    }

    public function editProfile(EditRequest $request)
    {
        $user = Auth::user();
        $user->name = $request->input('name');
        if ($request->avatar !=null) {
            $user->avatar_img = base64_encode(file_get_contents($request->avatar));
        }
        // if ($request->has('avatar')) {
        //     $fileName = $this->saveAvatar($request->file('avatar'));
        //     $user->avatar_file_name = $fileName;
        // }
        $user->save();

        return redirect()->back()->with('status', 'プロフィールを変更しました。');
    }

    /**
     * 画像をリサイズして保存します
     *
     * @param UploadedFile $file アップロードされた画像
     * @return string ファイル名
     */
    private function saveAvatar(UploadedFile $file)
    {
        $tempPath = $this->makeTempPath();
        Image::make($file)->fit(300, 300)->save($tempPath);
        $filePath = Storage::disk('public')->putFile('avatars', new File($tempPath));
        /**
         * 一時ファイルを生成してパスを取得する(makeTempPathメソッド)
         * Intervention Imageを使用して、画像をリサイズ後、一時ファイルに保存。
         * Storageファサードを使用して画像をディスクに保存しています。
         **/
        return basename($filePath);
    }

    /**
     * 一時的なファイルを生成してパスを返します。
     *
     * @return string ファイルパス
     */
    private function makeTempPath(): string
    {
        $tmp_fp = tmpfile(); // 以下のコードで一時ファイルを生成します。
        $meta   = stream_get_meta_data($tmp_fp); //以下のコードでファイルのメタ情報を取得します。
        return $meta["uri"];
    }
}
