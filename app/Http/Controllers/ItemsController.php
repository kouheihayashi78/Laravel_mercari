<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;

class ItemsController extends Controller
{
    public function showItems(Request $request)
    {
        $items = Item::orderByRaw("FIELD(state, '" . Item::STATE_SELLING . "', '" . Item::STATE_BOUGHT . "')")
            ->orderBy('id', 'DESC')
            ->paginate(40);
        // orderByRawメソッドを使って、出品中の商品を先に、購入済みの商品を後に表示
        // FIELDはSQLの関数で、第一引数で指定した値が第二引数以降の何番目に該当するかを返す。
        // stateがsellingの場合は1、boughtの場合は2を返しており、これを昇順で並べ替えることで、出品中(selling)の商品が先に、購入済み(bought)の商品が後になるようにソートされる。

        return view('items.items', compact('items', $items));
    }

    public function showItemDetail(Item $item)
    {
        // var_dump($item);
        return view('items.item_detail', compact('item', $item));
    }
}
