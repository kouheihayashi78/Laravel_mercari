<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Payjp\Charge;

class ItemsController extends Controller
{
    public function showItems(Request $request)
    {
        /////// カテゴリで絞り込み ///////
        $query = Item::query();
        if ($request->filled('category')) {
            // Requestインスタンスのfilledメソッドで、パラメータが指定されているかを調べることができる
            // hasメソッドがありますが、こちらは空文字列の場合もtrueを返すので、空の場合を考慮してfilledを使用する
            // explodeメソッドで文字列を分割(第一引数には、区切り文字(デリミタ)を指定する。)
            list($categoryType, $categoryID) = explode(':', $request->input('category'));
            if ($categoryType === 'primary') {
                $query->whereHas('secondaryCategory', function ($query) use ($categoryID) {
                    $query->where('primary_category_id', $categoryID);
                });
            } else if ($categoryType === 'secondary') {
                $query->where('secondary_category_id', $categoryID);
            }
        }

        /////// キーワードで絞り込み ///////
        if ($request->filled('keyword')) {
            // 前後にキーワードが含まれていればヒットする(% = 任意の文字列に一致する)
            // escapeメソッドは、特殊記号である%や_をエスケープする
            $keyword = '%' . $this->escape($request->input('keyword')) . '%';
            $query->where(function ($query) use ($keyword) {
                $query->where('name', 'LIKE', $keyword);
                $query->orwhere('description', 'LIKE', $keyword);
            });
        }

        $items = $query
            ->orderBy('id', 'DESC')
            ->paginate(40);
        // orderByRawメソッドを使って、出品中の商品を先に、購入済みの商品を後に表示
        // FIELDはSQLの関数で、第一引数で指定した値が第二引数以降の何番目に該当するかを返す。
        // stateがsellingの場合は1、boughtの場合は2を返しており、これを昇順で並べ替えることで、出品中(selling)の商品が先に、購入済み(bought)の商品が後になるようにソートされる。

        return view('items.items', compact('items', $items));
    }

    public function escape(string $value)
    {
        return str_replace(
            ['\\', '%', '_'],
            ['\\\\', '\\%', '\\_'],
            $value
        );
    }

    public function showItemDetail(Item $item)
    {
        // var_dump($item);
        return view('items.item_detail', compact('item', $item));
    }

    public function showBuyItemForm(Item $item)
    {
        if (!$item->isStateSelling) {
            abort(404); // abort関数でエラーページを返す
        }
        return view('items.item_buy_form', compact('item', $item));
    }

    public function buyItem(Request $request, Item $item)
    {
        $user = Auth::user();

        if (!$item->isState_Selling) {
            abort(404);
        }

        $token = $request->input('card-token');

        try {
            $this->settlement($item->id, $item->seller->id, $user->id, $token);
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()
                ->with('type', 'danger')
                ->with('message', '購入処理が失敗しました。');
        }

        return redirect()->route('item', [$item->id])
            ->with('message', '商品を購入しました。');
    }

    private function settlement($itemID, $sellerID, $buyerID, $token)
    {
        DB::beginTransaction();

        try {
            $seller = User::lockForUpdate()->find($sellerID);
            $item   = Item::lockForUpdate()->find($itemID);

            if ($item->isStateBought) {
                throw new \Exception('多重決済');
            }

            $item->state = Item::STATE_BOUGHT;
            $item->bought_at = Carbon::now();
            $item->buyer_id = $buyerID;
            $item->save();

            $seller->sales += $item->price;
            $seller->save();

            $charge = Charge::create([
                'card' => $token, // カードトークン
                'amount' => $item->price, // 金額
                'currency' => 'jpy' //通貨
            ]);

            if(!$charge->captured) {
                throw new \Exception('支払い確定失敗');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        DB::commit();
    }
}
