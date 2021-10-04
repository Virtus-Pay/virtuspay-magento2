define(
    ['mage/translate', 'Magento_Ui/js/model/messageList','jquery'],
    function ($t, messageList,$) {
        'use strict';
        return {
            validate: function () {
                if(jQuery('input[name="payment[method]"]:checked').val() === 'virtuspay') {
                    if (jQuery('#virtuspay-installments').val() === "") {
                        messageList.addErrorMessage({message: $t('Selecione um número de parcelas válido.')});
                        return false;
                    }
                }
/* DOB DISABLED
                if (jQuery("#virtuspay-dob").val().length != 10) {
                    messageList.addErrorMessage({ message: $t('Data de nascimento inválida') });
                    return false;
                }
                let date = jQuery("#virtuspay-dob").val(); // 10/10/1980
                date = date.substr(6,9) + '-' + date.substr(3,2) + '-' +  date.substr(0,2);
                console.log(date);
                var before = new Date(date);
                console.log(before.getFullYear());
                var now = new Date();
                if (before > now) {
                    messageList.addErrorMessage({ message: $t('Data de nascimento inválida') });
                    return false;
                }
 */
                return true;
            }
        }
    }
);
