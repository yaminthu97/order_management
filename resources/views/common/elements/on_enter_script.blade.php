<script>
        function setOnEnterClickButton(){
    //   var form = document.forms[0];
    var form = document.Form1;
    //   var targetButton = document.getElementById(targetButtonId);
    var targetButton = document.getElementById('{{$target_button_name}}');
      document.onkeypress=function(e){
        e = e ? e : event; 
        var keyCode= e.charCode ? e.charCode : ((e.which) ? e.which : e.keyCode);
        var elem = e.target ? e.target : e.srcElement;
        if(Number(keyCode) == 13) {//13=EnterKey
          if(!isIgnoreEnterKeySubmitElement(elem)){
            targetButton.click();
          }
          return isInputElement(elem) ? false : true;
        }
      }
    }
    function isIgnoreEnterKeySubmitElement(elem){
      var tag = elem.tagName;
      if(tag.toLowerCase() == "textarea"){
        return true;
      }
      switch(elem.type){
        case "button":
        case "submit":
        case "reset":
        case "image":
        case "file":
          return true;
      }
      return false;
    }
    function isInputElement(elem){
      switch(elem.type){
        case "text":
        case "radio":
        case "checkbox":
        case "password":
          return true;
      }
      return false;
    }
    document.Form1.onkeydown = setOnEnterClickButton();
</script>