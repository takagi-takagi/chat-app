$("#post-textarea").on("input", function() {
    var input = $(this).val(); //input に入力された文字を取得
    if(input){ //もし文字が入っていれば
        $("#submit-button").prop('disabled', false); //disabled を無効にする＝ボタンが押せる
        $("#submit-label").addClass("inversion");
    }else{
        $("#submit-button").prop('disabled', true); //disabled を有効にする＝ボタンが押せない
        $("#submit-label").removeClass("inversion");
    }
});
    