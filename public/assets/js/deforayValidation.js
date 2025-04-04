// this function helps us to check if a string starts with a specified substring or not
function startsWith(str, prefix) {
    return str.indexOf(prefix) === 0;
}

//function to check if a given element has a particular class or not

function hasClassName(objElement, strClass) {
    // if there is a class
    if (objElement.className) {
        // the classes are just a space separated list, so first get the list
        let arrList = objElement.className.split(' ');
        // get uppercase class for comparison purposes
        let strClassUpper = strClass.toUpperCase();
        // find all instances and remove them
        for (let className of arrList) {
            // if class found
            if (className.toUpperCase() == strClassUpper) {
                // we found it
                return true;
            }
        }
    }
    // if we got here then the class name is not there
    return false;

}


//deforayValidator literal
let deforayValidator = {
    init: function (settings) {
        this.settings = settings;
        this.form = document.getElementById(this.settings["formId"]);
        let formInputs = jQuery("input[type='text'],input[type='password'],textarea,select");

        // change color of inputs on focus
        for (let input of formInputs) {
            input.onfocus = function () {
                this.style.background = "#FFFFFF";
            }
        }
        let error = deforayValidator.validate();
        if (error == "" || error == null) {
            return true;
        } else {
            deforayValidator.printError(error);
            return false;
        }
    },
    validate: function () {
        error = '';
        this.form = document.getElementById(this.settings["formId"]);
        let formInputs = this.form.getElementsByTagName('*');
        let useTitleToShowMessage = true;
        if (this.settings["useTitle"] != 'undefined' && this.settings["useTitle"] != null && this.settings["useTitle"] == false) {
            useTitleToShowMessage = false;
        }

        error = deforayValidatorInternal(formInputs, useTitleToShowMessage);
        return error;
    },
    printError: function (error) {
        alert(error, 'err');
        return false;
    }
};
// returns true if the string is not empty
function isRequired(str) {
    return str == null || str.length == 0;
}
// returns true if the string is a valid email
function isEmail(str, required) {
    if (required) {
        if ((str == null || str.length == 0))
            return false;
    }
    else if (str != null && str.length != 0) {
        //        let re = /^[^\s()<>@,;:\/]+@\w[\w\.-]+\.[a-z]{2,}$/i
        let re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
        return re.test(str);
    }
    return true; // else return true

}
// returns true if the string only contains characters 0-9 and is not null
function isInputNumeric(str, required) {
    if (required && (str == null || str.length == 0)) {
        return false;
    } else if (str != "") {
        return !Number.isNaN(parseFloat(str)) && isFinite(str);
    }
}
// returns true if the string only contains characters A-Z or a-z
function isAlpha(str, required) {
    if (required) {
        if ((str == null || str.length == 0))
            return false;
    }
    let re = /[a-zA-Z]/;
    return re.test(str);
}
// returns true if the string only contains characters 0-9 A-Z or a-z
function isAlphaNum(str, required) {
    if (required) {
        if ((str == null || str.length == 0))
            return false;
    }
    // let re = /[0-9a-zA-Z]/
    let re = /^[0-9A-Za-z]+$/;
    return re.test(str);
}
// returns true if the string only contains characters OTHER THAN 0-9 A-Z or a-z
function isSymbol(str, required) {
    if (required) {
        if ((str == null || str.length == 0))
            return false;
    }
    let re = /[^0-9a-zA-Z]/;
    return re.test(str);
}
// checks if the string is of a specified minimum length or not
function minLength(str, len) {
    if ((str == null || str.length == 0)) {
        return false;
    }
    return str.length >= len;
}
// checks if the string is within a specified maximum length or not
function maxLength(str, len) {
    if ((str == null || str.length == 0)) {
        return false;
    }
    return str.length <= len;
}
// checks if the string is exactly equal to the specified length or not
function exactLength(str, len) {
    if ((str == null || str.length == 0)) {
        return false;
    }
    return str.length == len;
}
//confirm password validation
function confirmPassword(name) {
    let elements = document.getElementsByName(name);
    //assuming that there will be only 2 fields with this name
    return elements[0].value === elements[1].value;
}
//checkbox or radio required validation
function isRequiredCheckBox(name) {
    let flag = false;
    let elements = document.getElementsByName(name);
    let size = elements.length;

    for (let i = 0; i < size; i++) {
        if (elements[i].checked) {
            flag = true;
            break;
        }
    }
    return flag;
}
function findPos(obj) {

    let curleft = 0;
    let curtop = 0;
    if (obj.offsetParent) {
        curleft = obj.offsetLeft
        curtop = obj.offsetTop
        while (obj = obj.offsetParent) {
            curleft += obj.offsetLeft
            curtop += obj.offsetTop
        }
    }
    return [curleft, curtop];

}
function deforayValidatorInternal(formInputs, useTitleToShowMessage) {
    // change color of inputs on focus
    let valid = false;
    let elementTitle = "";
    let errorMsg = "";
    let innerParts = [];
    let valu = "";

    for (let i = 0; i < formInputs.length; i++) {
        let classes = formInputs[i].className;
        if (classes == "" || classes == null) {
            valid = true;
        }
        let parts = classes.split(" ");

        if (useTitleToShowMessage && formInputs[i].title != null && formInputs[i].title != "") {
            elementTitle = formInputs[i].title;
        }
        else {
            elementTitle = "";
        }
        for (let cCount = 0; cCount < parts.length; cCount++) {
            let required = false;
            if (parts[cCount] == "isRequired") {
                required = true;
                if (formInputs[i].type == 'checkbox' || formInputs[i].type == 'radio') {
                    valid = isRequiredCheckBox(formInputs[i].name);
                    if (elementTitle != null && elementTitle != "") {
                        errorMsg = elementTitle;
                    }
                    else {
                        errorMsg = "Please select " + formInputs[i].name;
                    }

                }
                else {
                    valu = formInputs[i].value;
                    valid = !isRequired(valu);
                    if (elementTitle != null && elementTitle != "") {
                        errorMsg = elementTitle;
                    }
                    else {
                        errorMsg = "This field is required";
                    }
                }
            }
            else if (parts[cCount] == "isEmail") {
                valu = formInputs[i].value;
                valid = isEmail(valu, required);
                if (elementTitle != null && elementTitle != "") {
                    errorMsg = elementTitle;
                }
                else {
                    errorMsg = "Please enter a valid email id";
                }
            }
            else if (parts[cCount] == "isNumeric") {
                valid = isInputNumeric(formInputs[i].value, required);
                if (elementTitle != null && elementTitle != "") {
                    errorMsg = elementTitle;
                }
                else {
                    errorMsg = "Please enter a valid number.";
                }
            }
            else if (parts[cCount] == "isAlpha") {
                valid = isAlpha(formInputs[i].value, required);
                if (elementTitle != null && elementTitle != "") {
                    errorMsg = elementTitle;
                }
                else {
                    errorMsg = "This field can only contain alphabets and numbers.";
                }
            }
            else if (parts[cCount] == "isAlphaNum") {
                valid = isAlphaNum(formInputs[i].value, required);
                if (elementTitle != null && elementTitle != "") {
                    errorMsg = elementTitle;
                }
                else {
                    errorMsg = "This field can only contain alphabets and numbers.";
                }
            }
            else if (parts[cCount] == "isSymbol") {
                valid = isSymbol(formInputs[i].value, required);
                if (elementTitle != null && elementTitle != "") {
                    errorMsg = elementTitle;
                }
                else {
                    errorMsg = "This field cannot contain alphabets and numbers.";
                }
            }
            else if (startsWith(parts[cCount], "minLength")) {
                innerParts = parts[cCount].split("_");
                valid = minLength(formInputs[i].value, innerParts[1]);
                if (elementTitle != null && elementTitle != "") {
                    errorMsg = elementTitle;
                }
                else {
                    errorMsg = "Minimum " + innerParts[1] + " characters required";
                }
            }
            else if (startsWith(parts[cCount], "maxLength")) {
                innerParts = parts[cCount].split("_");
                valid = maxLength(formInputs[i].value, innerParts[1]);
                if (elementTitle != null && elementTitle != "") {
                    errorMsg = elementTitle;
                }
                else {
                    errorMsg = "More than " + innerParts[1] + " characters not allowed";
                }
            }
            else if (startsWith(parts[cCount], "exactLength")) {
                innerParts = parts[cCount].split("_");
                valid = exactLength(formInputs[i].value, innerParts[1]);
                if (elementTitle != null && elementTitle != "") {
                    errorMsg = elementTitle;
                }
                else {
                    errorMsg = "This field should have exactly " + innerParts[1] + " characters";
                }
            }
            else if (parts[cCount] == "confirmPassword") {
                valid = confirmPassword(formInputs[i].name);
                if (elementTitle != null && elementTitle != "") {
                    errorMsg = elementTitle;
                }
                else {
                    errorMsg = "Please make sure password and confirm password are the same";
                }
            }
            else {
                valid = true;
            }
            if (!valid) {
                formInputs[i].style.background = "#FFFF99";
                formInputs[i].style.border = "1px solid #CF3339";
                $('.infocus').removeClass('infocus');
                $(formInputs[i]).addClass('infocus');
                return errorMsg;
            }
        }
    }
}

let dateFormat = function () {
    let token = /d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,
        timezone = /\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,
        timezoneClip = /[^-+\dA-Z]/g,
        pad = function (val, len) {
            val = String(val);
            len = len || 2;
            while (val.length < len) val = "0" + val;
            return val;
        };

    // Regexes and supporting functions are cached through closure
    return function (date, mask, utc) {
        let dF = dateFormat;

        // You can't provide utc if you skip other args (use the "UTC:" mask prefix)
        if (arguments.length == 1 && Object.prototype.toString.call(date) == "[object String]" && !/\d/.test(date)) {
            mask = date;
            date = undefined;
        }

        // Passing date through Date applies Date.parse, if necessary
        date = date ? new Date(date) : new Date;
        if (Number.isNaN(date)) throw SyntaxError("invalid date");

        mask = String(dF.masks[mask] || mask || dF.masks["default"]);

        // Allow setting the utc argument via the mask
        if (mask.slice(0, 4) == "UTC:") {
            mask = mask.slice(4);
            utc = true;
        }

        let _ = utc ? "getUTC" : "get",
            d = date[_ + "Date"](),
            D = date[_ + "Day"](),
            m = date[_ + "Month"](),
            y = date[_ + "FullYear"](),
            H = date[_ + "Hours"](),
            M = date[_ + "Minutes"](),
            s = date[_ + "Seconds"](),
            L = date[_ + "Milliseconds"](),
            o = utc ? 0 : date.getTimezoneOffset(),
            flags = {
                d: d,
                dd: pad(d),
                ddd: dF.i18n.dayNames[D],
                dddd: dF.i18n.dayNames[D + 7],
                m: m + 1,
                mm: pad(m + 1),
                mmm: dF.i18n.monthNames[m],
                mmmm: dF.i18n.monthNames[m + 12],
                yy: String(y).slice(2),
                yyyy: y,
                h: H % 12 || 12,
                hh: pad(H % 12 || 12),
                H: H,
                HH: pad(H),
                M: M,
                MM: pad(M),
                s: s,
                ss: pad(s),
                l: pad(L, 3),
                L: pad(L > 99 ? Math.round(L / 10) : L),
                t: H < 12 ? "a" : "p",
                tt: H < 12 ? "am" : "pm",
                T: H < 12 ? "A" : "P",
                TT: H < 12 ? "AM" : "PM",
                Z: utc ? "UTC" : (String(date).match(timezone) || [""]).pop().replace(timezoneClip, ""),
                o: (o > 0 ? "-" : "+") + pad(Math.floor(Math.abs(o) / 60) * 100 + Math.abs(o) % 60, 4),
                S: ["th", "st", "nd", "rd"][d % 10 > 3 ? 0 : (d % 100 - d % 10 != 10) * d % 10]
            };

        return mask.replace(token, function ($0) {
            return $0 in flags ? flags[$0] : $0.slice(1, $0.length - 1);
        });
    };
}();

// Some common format strings
dateFormat.masks = {
    default: "ddd mmm dd yyyy HH:MM:ss",
    shortDate: "m/d/yy",
    mediumDate: "mmm d, yyyy",
    longDate: "mmmm d, yyyy",
    fullDate: "dddd, mmmm d, yyyy",
    shortTime: "h:MM TT",
    mediumTime: "h:MM:ss TT",
    longTime: "h:MM:ss TT Z",
    isoDate: "yyyy-mm-dd",
    isoTime: "HH:MM:ss",
    isoDateTime: "yyyy-mm-dd'T'HH:MM:ss",
    isoUtcDateTime: "UTC:yyyy-mm-dd'T'HH:MM:ss'Z'",
};

// Internationalization strings
dateFormat.i18n = {
    dayNames: [
        "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat",
        "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"
    ],
    monthNames: [
        "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
        "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"
    ]
};

// Utility function for formatting dates
function formatDate(date, mask, utc) {
    return dateFormat(date, mask, utc);
}
