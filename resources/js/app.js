/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

document.querySelector('.image-picker input') //画像を選択するinputタグのDOMを取得
    .addEventListener('change', (e) => {
        /* 
        第一引数は処理を追加するイベントの種類を指定。
        第二引数は関数(リスナー)を指定。イベントを検出した時にこの関数が実行される。 
        */
        const input = e.target;
        const reader = new FileReader();
        reader.onload = (e) => {
            //imgタグのsrc属性を更新するために、imgタグのDOMを取得
            //closestメソッドは親方向に向かってDOMを検索
            input.closest('.image-picker').querySelector('img').src = e.target.result
            //読み込んだ結果をimgタグのsrcフィールドに代入
        };
        reader.readAsDataURL(input.files[0]);
        // readAsDataURLメソッドで画像の読み込みを開始
    });

window.Vue = require('vue').default;

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('example-component', require('./components/ExampleComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
});
