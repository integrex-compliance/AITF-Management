


function validateReq(id,len = 0){
    if($(id).val()=="" || $(id).val()=="na"){
        $(id).closest(".form-group").addClass("has-error");
        return false;
    } else {
        if($(id).val().length >= len) {
          $(id).closest(".form-group").removeClass("has-error");
          return true;
        } else {
          $(id).closest(".form-group").addClass("has-error");
          return false;
        }
    }
}

function onlyNumerics(e){
    var keyCode = (e.keyCode ? e.keyCode : e.which);
    if (keyCode == 187 || keyCode == 69 || keyCode == 101 || keyCode == 189 || keyCode == 42 || keyCode == 43 || keyCode == 45 || keyCode == 46){
        e.preventDefault();
    }
}
function preventNumbers(e){
    var keyCode = (e.keyCode ? e.keyCode : e.which);
    //if (!(keyCode >= 65 && keyCode <= 107) && (keyCode != 32 && keyCode != 0 && keyCode != 89 && keyCode != 90)) {
    //if (keyCode >= 48 && keyCode <= 57 || keyCode >= 96 && keyCode <= 105 ){
    if (keyCode >= 48 && keyCode <= 57){
        e.preventDefault();
    }
}

function validateEither(elem1,elem2){
  if($(elem1).val()=="" && $(elem2).val()==""){
    $(elem1).closest(".form-group").addClass("has-error");
    $(elem2).closest(".form-group").addClass("has-error");
    return false;
  } else {
    $(elem1).closest(".form-group").removeClass("has-error");
    $(elem2).closest(".form-group").removeClass("has-error");
    return true;
  }
}

function validatePassw(elem1,elem2) {
  var flag = false;
  if($(elem1).val()!="" && $(elem2).val()!=""){
    if ($(elem2).val()!=$(elem1).val()) {
      $(elem2).closest(".form-group").addClass("has-error");
      flag = false;
    } else {
      $(elem2).closest(".form-group").removeClass("has-error");
      flag = true;
    }
  }
  return flag;
}

function validateEmail(id){
    var a = $(id).val();
    var filter = /^[a-zA-Z0-9]+[a-zA-Z0-9_.-]+[a-zA-Z0-9_-]+@[a-zA-Z0-9]+[a-zA-Z0-9.-]+[a-zA-Z0-9]+.[a-z]{2,4}$/;

    if(filter.test(a)){
        $(id).closest(".form-group").removeClass("has-error");
        return true;
    } else {
        $(id).closest(".form-group").addClass("has-error");
        return false;
    }
}

function validateWebsite(id){
    var a = $(id).val();
    var filter = /^((https?|ftp|smtp):\/\/)?(www.)?[a-z0-9]+\.[a-z]+(\/[a-zA-Z0-9#]+\/?)*$/;
    //var filter = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
    if(a.trim() !== "") {
        if(filter.test(a)){
            $(id).closest(".form-group").removeClass("has-error");
            return true;
        } else {
            $(id).closest(".form-group").addClass("has-error");
            return false;
        }
    } else {
        $(id).closest(".form-group").removeClass("has-error");
        return true;
    }
}