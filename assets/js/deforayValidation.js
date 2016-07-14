// this function helps us to check if a string starts with a specified substring or not
if(!String.prototype.startsWith){
    String.prototype.startsWith = function (str) {
        return !this.indexOf(str);
    }
}

//function to check if a given element has a particular class or not

function hasClassName(objElement, strClass){
   // if there is a class
   if ( objElement.className )
      {
      // the classes are just a space separated list, so first get the list
      var arrList = objElement.className.split(' ');
      // get uppercase class for comparison purposes
      var strClassUpper = strClass.toUpperCase();
      // find all instances and remove them
      for ( var i = 0; i < arrList.length; i++ )
         {
         // if class found
         if ( arrList[i].toUpperCase() == strClassUpper )
            {
            // we found it
            return true;
            }
         }
      }
   // if we got here then the class name is not there
   return false;

   }


//deforayValidator literal
var deforayValidator = {
	init: function (settings) {
		this.settings = settings;
		this.form = document.getElementById(this.settings["formId"]);
		//console.log(this.form);
		formInputs = jQuery("input[type='text'],input[type='password'],textarea,select");

		// change color of inputs on focus
		for(i=0;i<formInputs.length;i++)
		{
			formInputs[i].onfocus = function () {
				this.style.background = "#FFFFFF";
			}
		};
		var error = deforayValidator.validate();
		if(error == "" || error == null){
			return true;
		} else {
			deforayValidator.printError(error);
			return false;
		}
	},
	validate: function () {
		error = '';
		this.form = document.getElementById(this.settings["formId"]);
		formInputs = this.form.getElementsByTagName('*');
                useTitleToShowMessage = true;
                if(this.settings["useTitle"] != 'undefined' && this.settings["useTitle"] != null && this.settings["useTitle"] == false){
                    useTitleToShowMessage = false;
                }

		error = deforayValidatorInternal(formInputs , useTitleToShowMessage);
		return error;
	},
	printError: function (error) {
		alert(error,'err');
		return false;
	}
};
// returns true if the string is not empty
function isRequired(str){
    if(str == null || str.length == 0){
        return true;
    }
    else{
        return false;
    }
//return (str == null) || (str.length == 0);
}
// returns true if the string is a valid email
function isEmail(str,required){
    if(required){
        if((str == null || str.length == 0))
            return false;
    }
    else if(str != null && str.length != 0){
//        var re = /^[^\s()<>@,;:\/]+@\w[\w\.-]+\.[a-z]{2,}$/i
        var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
        return re.test(str);
    }
    return true; // else return true

}
// returns true if the string only contains characters 0-9 and is not null
function isNumeric(str,required){
    if(required){
        if((str == null || str.length == 0))
            return false;
    }else{
        if(str!="")
        return !isNaN(parseFloat(str)) && isFinite(str);
    }
    return true;
}
// returns true if the string only contains characters A-Z or a-z
function isAlpha(str,required){
    if(required){
        if((str == null || str.length == 0))
            return false;
    }
    var re = /[a-zA-Z]/;
    if (re.test(str)) return true;
    return false;
}
// returns true if the string only contains characters 0-9 A-Z or a-z
function isAlphaNum(str,required){
    if(required){
        if((str == null || str.length == 0))
            return false;
    }
    // var re = /[0-9a-zA-Z]/
    var re =  /^[0-9A-Za-z]+$/;
    if (re.test(str)) return true;
    return false;
}
// returns true if the string only contains characters OTHER THAN 0-9 A-Z or a-z
function isSymbol(str,required){
    if(required){
        if((str == null || str.length == 0))
            return false;
    }
    var re = /[^0-9a-zA-Z]/;
    if (re.test(str)) return true;
    return false;
}
// checks if the string is of a specified minimum length or not
function minLength(str,len){
    if((str == null || str.length == 0)) return false;
    if(str.length < len){
        return false;
    }
    else{
        return true;
    }
}
// checks if the string is within a specified maximum length or not
function maxLength(str,len){
    if((str == null || str.length == 0)) return false;
    if(str.length > len){
        return false;
    }
    else{
        return true;
    }
}
// checks if the string is exactly equal to the specified length or not
function exactLength(str,len){
    if((str == null || str.length == 0)) return false;
    if(str.length == len){
        return true;
    }
    else{
        return false;
    }
}
//confirm password validation
function confirmPassword(name){
    var elements = document.getElementsByName(name);
    //assuming that there will be only 2 fields with this name

    if(elements[0].value == elements[1].value){
        return true;
    }
    else{
        return false;
    }

}
//checkbox or radio required validation
function isRequiredCheckBox(name){
    var flag = false;
    var elements = document.getElementsByName(name);
    size = elements.length;
    count = 0;

    for(var i=0;i <size;i++){
        if(elements[i].checked){
            flag = true;
            break;
        }
        else{
            continue;
        }
    }
    return flag;
}
function findPos(obj) {

var curleft = curtop = 0;
if (obj.offsetParent) {
        curleft = obj.offsetLeft
        curtop = obj.offsetTop
        while (obj = obj.offsetParent) {
                curleft += obj.offsetLeft
                curtop += obj.offsetTop
        }
}
return [curleft,curtop];

}
function deforayValidatorInternal(formInputs, useTitleToShowMessage){
		// change color of inputs on focus
	for(i=0;i<formInputs.length;i++){
		classes = formInputs[i].className;
		if(classes == "" || classes== null){
			valid = true;
		}
		var parts = classes.split(" ");

                if(hasClassName(formInputs[i],"useTitle")){

                    elementTitle = formInputs[i].title;
                }
                else if(useTitleToShowMessage){
                    elementTitle = formInputs[i].title;
                }
                else{
                    elementTitle = "";
                }
		for(var cCount=0; cCount< parts.length; cCount++){
                    var required = false;
			if(parts[cCount] == "isRequired"){
                            required = true;
                            if(formInputs[i].type == 'checkbox' || formInputs[i].type == 'radio'){
				valid = isRequiredCheckBox(formInputs[i].name);
				if(elementTitle != null && elementTitle != "")
				{
					errorMsg = elementTitle;
				}
				else{
					errorMsg = "Please select "+formInputs[i].name;
				}

                            }
                            else{

                                    var valu = (formInputs[i].value);
                                    valid = !isRequired(valu);
                                    if(elementTitle != null && elementTitle != "")
                                    {
                                            errorMsg = elementTitle;
                                    }
                                    else{
                                            errorMsg = "Please don't leave this field blank";
                                    }
				}
			}
			else if(parts[cCount] == "isEmail"){
				var valu = (formInputs[i].value);
				valid = isEmail(valu,required);
				if(elementTitle != null && elementTitle != "")
				{
					errorMsg = elementTitle;
				}
				else{
					errorMsg = "Please enter a valid email id";
				}
			}
			else if(parts[cCount] == "isNumeric"){
				valid = isNumeric(formInputs[i].value,required);
				if(elementTitle != null && elementTitle != "")
				{
					errorMsg = elementTitle;
				}
				else{
					errorMsg = "Please enter a valid number.";
				}
			}
			else if(parts[cCount] == "isAlpha"){
				valid = isAlpha(formInputs[i].value,required);
				if(elementTitle != null && elementTitle != "")
				{
					errorMsg = elementTitle;
				}
				else{
					errorMsg = "This field can only contain alphabets and numbers.";
				}
			}
			else if(parts[cCount] == "isAlphaNum" ){
				valid = isAlphaNum(formInputs[i].value,required);
				if(elementTitle != null && elementTitle != "")
				{
					errorMsg = elementTitle;
				}
				else{
					errorMsg = "This field can only contain alphabets and numbers.";
				}
			}
			else if(parts[cCount] == "isSymbol"){
				valid = isSymbol(formInputs[i].value,required);
				if(elementTitle != null && elementTitle != "")
				{
					errorMsg = elementTitle;
				}
				else{
					errorMsg = "This field cannot contain alphabets and numbers.";
				}
			}
			else if(parts[cCount].startsWith("minLength")){
				innerParts = parts[cCount].split("_");
				valid = minLength(formInputs[i].value,innerParts[1]);
				if(elementTitle != null && elementTitle != "")
				{
					errorMsg = elementTitle;
				}
				else{
					errorMsg = "Minimum "+innerParts[1]+" characters required";
				}
            }
			else if(parts[cCount].startsWith("maxLength")){
				innerParts = parts[cCount].split("_");
				valid = maxLength(formInputs[i].value,innerParts[1]);
				if(elementTitle != null && elementTitle != "")
				{
					errorMsg = elementTitle;
				}
				else{
					errorMsg = "More than "+innerParts[1]+" characters not allowed";
				}
			}
			else if(parts[cCount].startsWith("exactLength")){
				innerParts = parts[cCount].split("_");
				valid = exactLength(formInputs[i].value,innerParts[1]);
				if(elementTitle != null && elementTitle != "")
				{
					errorMsg = elementTitle;
				}
				else{
					errorMsg = "This field should have exactly "+innerParts[1]+" characters";
				}
			}
			else if(parts[cCount] == "confirmPassword"){
				valid = confirmPassword(formInputs[i].name);
				if(elementTitle != null && elementTitle != "")
				{
					errorMsg = elementTitle;
				}
				else{
					errorMsg = "Please make sure password and confirm password are same";
				}
			}
			else{
				valid = true;
			}
			if(!valid) {
				formInputs[i].style.background = "#FFFF99";
				formInputs[i].style.border = "1px solid #CF3339";
				$('.infocus').removeClass('infocus');
				$(formInputs[i]).addClass('infocus');
				//formInputs[i].focus();
				//myPos = findPos(formInputs[i]);
				//scrollTo(myPos[0],myPos[1]);

				return errorMsg;
			}
		}
    }
}