


function raiseSuccessfullToast(message){
    toastr.success(message, 'Success', {positionClass: "toast-top-right", escapeHtml: true})
}

function raiseFailToast(message){
    toastr.error(message, 'Error', {positionClass: "toast-top-right"})
}

function raiseToastr(type, message){
    if(type == "success"){
        raiseSuccessfullToast(message)
    }else if(type == "error"){
        raiseFailToast(message)
    }
}
