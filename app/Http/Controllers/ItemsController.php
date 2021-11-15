<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;

class ItemsController extends Controller
{
    public function showItems(Request $request)
    {
        /////// カテゴリで絞り込み ///////
        $query = Item::query();
        if($request->filled('category')) {
            // Requestインスタンスのfilledメソッドで、パラメータが指定されているかを調べることができる
            // hasメソッドがありますが、こちらは空文字列の場合もtrueを返すので、空の場合を考慮してfilledを使用する
            // explodeメソッドで文字列を分割(第一引数には、区切り文字(デリミタ)を指定する。)
            list($categoryType, $categoryID) = explode(':', $request->input('category'));
            if($categoryType === 'primary') {
                $query->whereHas('secondaryCategory', function ($query) use ($categoryID) {
                    $query->where('primary_category_id', $categoryID);
                });
            }else if($categoryType === 'secondary'){
                $query->where('secondary_category_id', $categoryID);
            }
        }

        /////// キーワードで絞り込み ///////
        if($request->filled('keyword')) {
            // 前後にキーワードが含まれていればヒットする(% = 任意の文字列に一致する)
            // escapeメソッドは、特殊記号である%や_をエスケープする
            $keyword = '%'. $this->escape($request->input('keyword')). '%';
            $query->where(function ($query) use ($keyword) {
                $query->where('name', 'LIKE', $keyword);
                $query->orwhere('description', 'LIKE', $keyword);
            });
        }

        $items = $query->orderByRaw("FIELD(state, '" . Item::STATE_SELLING . "', '" . Item::STATE_BOUGHT . "')")
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
}
