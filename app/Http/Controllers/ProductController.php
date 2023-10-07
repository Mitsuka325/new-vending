<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;


class ProductController extends Controller
{

    // 一覧ページ
    // リクエストオブジェクトからcompany_idおよびproduct_nameパラメータをチェック
    // もしcompany_idのメータが存在する場合、指定されたcompany_idに対する製品を取得
    //またproduct_nameメータがあればその名前でフィルタリング⇒ページネーション適用2件ずつ取得
    //もしcompany_idメータが存在しないが、product_nameメータが存在する場合、product_nameでフィルタリングして、ページネーション適用2件ずつ取得
    // それ以外は、全ての製品を2件ずつ取得
    // 製品の取得はproductモデルのスコープメソッドを利用して行われる
    // Product::companyId($request->company_id) や Product::productName($request->product_name) は、Product モデルに定義されたスコープメソッド
    public function index(Request $request)
    {

        if ($request->company_id) {
            $products = Product::companyId($request->company_id)
                ->productName($request->product_name)
                ->paginate(2);
        } elseif ($request->product_name) {
            $products = product::productName($request->product_name)->paginate(2);
        } else {
            $products = product::paginate(2);
        }
        return view('products.index', compact('products'));
    }
    // 作成ページ
    public function create()
    {
        return view('products.create');
    }

    // 作成機能
    public function store(ProductRequest $request)
    {
        DB::beginTransaction(); //トランザクションを開始
        try {
            $validatedData = $request->validated(); // バリデーション済みデータ取得

            $product = new Product();
            $product->fill($validatedData);

            //  商品画像の保存
            // フォームから送信されたリクエストの中に
            //  'img_path' というファイルが存在するか確認し、
            // 存在する場合にそのファイルを 'images' ディレクトリに保存し、
            // その保存されたファイルのパスを Product モデルの img_path に格納
            if ($request->hasFile('img_path')) {
                $imgPath = $request->file('img_path')->store('images');
                $product->img_path = $imgPath;
                $product->save(); //データベースへの保存
            } else {
                $product->save();
            }
            DB::commit(); //トランザクションをコミット
            return redirect()->route('products.index')->with('flash_message', '商品の登録が完了しました');
            // リダイレクトフラッシュメッセージの表示 layouts/app.bladeにsessionの設定をする
        } catch (\Throwable $th) {
            DB::rollBack(); //エラーがあった場合はロールバック
            return back()->withErrors(['error' => '商品の作成に失敗しました']);
        }
    }
    // 詳細ページ 
    // compact関数で変数をビューに渡す
    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    // 更新ページ
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }


    // 更新処理
    public function update(ProductRequest $request, Product $product)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated(); // バリデーション済みデータ取得
            $product->fill($validatedData);

            if ($request->hasFile('img_path')) {
                $imgPath = $request->file('img_path')->store('images');
                $product->img_path = $imgPath;
            }
            $product->save();
            DB::commit();
            return redirect()->route('products.index')->with('flash_message', '商品の更新が完了しました');
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->withErrors(['error' => '商品の更新に失敗しました']);
        }
    }

    // 削除機能
    public function destroy(Product $product)
    {
        DB::beginTransaction();
        try {
            $product->delete();
            DB::commit();
            return redirect()->route('products.index')->with('flash_message', '商品を削除しました');
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->withErrors(['error' => '商品の削除に失敗しました']);
        }
    }
}
