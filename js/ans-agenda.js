$(function () {

	$.datepicker.setDefaults( $.datepicker.regional[ "es" ] );

  $("#datepicker").datepicker({
    beforeShowDay: function (date) {
      var day = date.getDay();
      return [(day != 0 && day != 6)];
    },
    dateFormat: "dd-mm-yy",
    minDate: -2,
    maxDate: +3
  });
  
  $("#datepicker").on("change",function(){
    var selected = $(this).val();
    $('#boton-elegir-hora').hide();
    $('#boton-cambiar-hora').hide();
    $('#spinner').show();
    $('#selected-hour').html("");
    getHours();
  });

  $('#hoursModal #hours-content').on('click','a', function(e){
  	e.preventDefault();
    $('#hoursModal #hours-content a').removeClass('active');
    $(this).addClass('active');
  	$("#reg-form #hour").val(e.target.id);
    $("#reg-form #customer_hour").val(e.target.innerText);
    $("#selected-hour").html(e.target.innerText);
    $('#hoursModal').modal('toggle');
    document.getElementById("customer-form").style.display = "block";
  });

  // Modal hours close
  $('#hoursModal').on('hidden.bs.modal', function (e) {
    // if hour empty then select first hour available
    if ( $("#selected-hour").html().length == 0 ) {
      $('#selected-hour').html($("#hours-content a").first().html());
    }
    $('#boton-elegir-hora').hide();
    $('#boton-cambiar-hora').show();
  });

  $('#paisModal #country').on('input',function(e){
    var input = e.target,
      list = input.getAttribute('list'),
      options = document.querySelectorAll('#' + list + ' option'),
      hiddenInput = document.getElementById(input.id + '-hidden'),
      inputValue = input.value;
    for(var i = 0; i < options.length; i++) {
      var option = options[i];
      if(option.innerText === inputValue) {
        hiddenInput.value = option.getAttribute('data-value');
        $('#selected-pais').html(inputValue);
        $('#selected-pais-code').html(option.getAttribute('data-value'));
        $("#reg-form #country").val(inputValue);
        getTimeZones(option.getAttribute('data-value'));
        break;
      }
    }
  });

  $('#timezones').on('change', function(e){
    $('#selected-zone').html(this.value);
    $("#reg-form #customer_timezone").val(this.value);
    getHours();
  });

  // Validación campos on-time
  $('#reg-form').on('keyup', 'input#name', function(e){
    verifyName(this.value);
  });

  $('#reg-form').on('keyup', 'input#email', function(e){
    verifyEmail(this.value);
  })

  $('#reg-form').on('keyup', 'input#phone', function(e){
    verifyPhone(this.value);
  });

  // Submit formulario de toma de datos
  $("#submit-button").on('click', function(e){
    e.preventDefault();
    
    if (verifyFormFields()) {

      $('#form-data').hide();
      $('#resevating').show();
      $('#service-title strong').html('Reservando la cita...');

      var data = {
        'name': $("#reg-form #name").val(),
        'country': $("#reg-form #country").val(),
        'email': $("#reg-form #email").val(),
        'phone': $("#reg-form #phone").val(),
        'date': $("#reg-form #date").val(),
        'hour': $("#reg-form #hour").val(),
        'customer_hour': $("#reg-form #customer_hour").val(),
        'customer_timezone': $("#reg-form #customer_timezone").val(),
        'amount': $("#reg-form #price").val(),
        'pay_mode': $("input[name='pay_mode']:checked").val(),
      }
     
      $('#confirm-data #confirm-data-name span').html(data.name);
      $('#confirm-data #confirm-data-country span').html(data.country);
      $('#confirm-data #confirm-data-email span').html(data.email);
      $('#confirm-data #confirm-data-phone span').html(data.phone);
      $('#confirm-data #confirm-data-date span').html(data.date);
      $('#confirm-data #confirm-data-hour span').html(data.customer_hour.substr(0,5));
      $('#confirm-data #confirm-data-amount span').html(data.amount);
      $('#confirm-data #confirm-data-pay-mode span').html(data.pay_mode);

      if (data.pay_mode === "visa") {
        //generateVisaForm(data.amount);
        $.post(
          PT_Ajax.ajaxurl, {
            action: 'ansapp_ajax_redsys_pay',
            amount: data.amount,
            nonce: PT_Ajax.nonce
          },
          function(response) {
            $('#pay-form').html(response);
            document.getElementById("form-data").style.display = "none";
            document.getElementById("confirm-data").style.display = "block";
          }
        )
        .done(function(){
        });
      } else if (data.pay_mode === "paypal") {
        // pay vía paypal obtain paypal url
        $.post(
          PT_Ajax.ajaxurl, {
            action: 'ansapp_ajax_paypal_pay',
            amount: data.amount,
            nonce: PT_Ajax.nonce
          },
          function(r) {
            var resp = JSON.parse(r);
            var formMessages = $('#form-messages');
            var data = {
              'payment_id': resp.payment_id,
              'name': $("#reg-form #name").val(),
              'country': $("#reg-form #country").val(),
              'email': $("#reg-form #email").val(),
              'phone': $("#reg-form #phone").val(),
              'date': $("#reg-form #date").val(),
              'hour': $("#reg-form #hour").val(),
              'customer_hour': $("#reg-form #customer_hour").val(),
              'customer_timezone': $("#reg-form #customer_timezone").val(),
              'amount': $("#reg-form #price").val(),
              'pay_mode': $("input[name='pay_mode']:checked").val(),
            }
           
            saveData(data, resp.payment_id, function(){
              $('#pay-form').html(resp.form);
              document.getElementById("form-data").style.display = "none";
              document.getElementById("confirm-data").style.display = "block";
              $('#resevating').hide();
              $('#service-title strong').html('Cita reservada');
            });
          }
        )
        .done(function(){
        });
      } else {
        // Pago por transferencia
        window.location.href = "http://xanatarot.com/cita-pago-por-transferencia/";
        
        // $.post(
        //   PT_Ajax.ajaxurl, {
        //     action: 'ansapp_ajax_transferencia_pay',
        //     amount: data.amount,
        //     nonce: PT_Ajax.nonce
        //   },
        //   function(r) {
        //     var resp = JSON.parse(r);
        //     var formMessages = $('#form-messages');
        //     var data = {
        //       'payment_id': resp.payment_id,
        //       'name': $("#reg-form #name").val(),
        //       'country': $("#reg-form #country").val(),
        //       'email': $("#reg-form #email").val(),
        //       'phone': $("#reg-form #phone").val(),
        //       'date': $("#reg-form #date").val(),
        //       'hour': $("#reg-form #hour").val(),
        //       'customer_hour': $("#reg-form #customer_hour").val(),
        //       'customer_timezone': $("#reg-form #customer_timezone").val(),
        //       'amount': $("#reg-form #price").val(),
        //       'pay_mode': $("input[name='pay_mode']:checked").val(),
        //     }

        //     saveData(data, data.payment_id, function(){
        //       $('#pay-form').html(resp.form);
        //       document.getElementById("form-data").style.display = "none";
        //       document.getElementById("confirm-data").style.display = "block";
        //       $('#resevating').hide();
        //       $('#service-title strong').html('Cita reservada');
        //     });
        //   }
        // )
        // .done(function(){
        // });
      }
    }
  });

  // botón atrás del formulario de confirmación
  $('#confirm-data').on('click','#return-form', function(e){
    e.preventDefault();
    document.getElementById("form-data").style.display = "block";
    document.getElementById("confirm-data").style.display = "none";
  });

  // formulario de confirmación y envío a redsys
  $('#confirm-data').on('submit','#redsys-form',function(e){
    
    e.preventDefault();
    
    var formMessages = $('#form-messages');
    var data = {
      'name': $("#reg-form #name").val(),
      'country': $("#reg-form #country").val(),
      'email': $("#reg-form #email").val(),
      'phone': $("#reg-form #phone").val(),
      'date': $("#reg-form #date").val(),
      'hour': $("#reg-form #hour").val(),
      'customer_hour': $("#reg-form #customer_hour").val(),
      'customer_timezone': $("#reg-form #customer_timezone").val(),
      'amount': $("#reg-form #price").val(),
      'pay_mode': $("input[name='pay_mode']:checked").val(),
    }
    
    $.post(
      PT_Ajax.ajaxurl, {
        action: 'ans_agenda_ajax_save_form',
        data: $("#reg-form").serialize(),
        name: data.name,
        country: data.country,
        email: data.email,
        phone: data.phone,
        date: data.date,
        hour: data.hour,
        customer_hour: data.customer_hour,
        customer_timezone: data.customer_timezone,
        amount: data.amount,
        pay_mode: data.pay_mode,
        nonce: PT_Ajax.nonce
      },

      function(response) {
        if (response) {
          $('#confirm-data').unbind('submit');
          $('#redsys-form').submit();
        } 
      }
    )
    .done(function(){
    });
  });

  function getTimeZones(country_code) {
    $.post(
      PT_Ajax.ajaxurl, {
        action: 'ans_agenda_ajax_get_timezone',
        country_code: country_code,
        nonce: PT_Ajax.nonce
      },
      function(response) {
        if (response) {
          var obj = JSON.parse(response);
          var template = "";
          Object.keys(obj).forEach(function(e){
            template += '<option value="'+obj[e]+'">'+obj[e]+'</option>';
          });
          $('#timezones').html(template);
          $('#timezones').show();
          $('#selected-zone').html($("#timezones option:first").val());
          $("#reg-form #customer_timezone").val($("#timezones option:first").val());
          $("#selected-hour").html("");
          $("#boton-cambiar-hora").hide();
          getHours();
        }
      }
    );
  }

  function getHours() {
    var country_timezone = $('#selected-zone').html();
    var date = $('#datepicker').val();
    $.post(
      PT_Ajax.ajaxurl, {
        action: 'ans_agenda_ajax_show_calendar',
        date: date,
        country_timezone: country_timezone,
        nonce: PT_Ajax.nonce
      },

      function(response) {
        if (response) {
          $('#hours-content').html(response);
          $('#spinner').hide();
          $('#boton-cambiar-hora').hide();
          $('#boton-elegir-hora').show();
          $("#reg-form #date").val(date);
        } else {
          console.log('fail...');
        }
      }
    );
  }

  function saveData(data, payment_id, callback) {
    $.post(
      PT_Ajax.ajaxurl, {
        action: 'ans_agenda_ajax_save_form',
        //data: $("#reg-form").serialize(),
        payment_id: payment_id,
        name: data.name,
        country: data.country,
        email: data.email,
        phone: data.phone,
        date: data.date,
        hour: data.hour,
        customer_hour: data.customer_hour,
        customer_timezone: data.customer_timezone,
        amount: data.amount,
        pay_mode: data.pay_mode,
        nonce: PT_Ajax.nonce
      },

      function(response) {
        if (response) {
          callback();
        } else {
          console.log('fail...');
        }
      }
    )
    .done(function(xhr, status, error){
    })
    .fail(function(xhr, status, error) {
      console.log(xhr);
      console.log(status);
      console.log(error);
    });
  }

  function verifyName(value) {
    var error_name = $('#error_name');
    var test = value.length != 0;
    if (test) {
      error_name.html("");
      error_name.hide();
    } else {
      error_name.html("El campo nombre no puede estar vacío");
      error_name.show();
    }
    return test;
  }

  function  verifyEmail(value) {
    var emailRegExp = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
    var error_email = $('#error-email');
    var test = value.length === 0 || emailRegExp.test(value);
    if (test) {
      error_email.html("");
      error_email.hide();
    } else {
      error_email.html("Teclea un email válido");
      error_email.show();
    }
    return test;
  }

  function verifyEmailEmpty(value) {
    var error_email = $('#error-email');
    var test = value.length != 0;
    if (test) {
      error_email.html("");
      error_email.hide();
    } else {
      error_email.html("Teclea un email válido");
      error_email.show();
    }
    return test;
  }

  function verifyPhone(value) {
    var telefonoRegExp = /^\d{9}$/;
    var error_telefono = $('#error_phone');
    var test = value.length != 0 && telefonoRegExp.test(value);
    if (test) {
      error_telefono.html("");
      error_telefono.hide();
    } else {
      error_telefono.html("Teclea un número de teléfono válido");
      error_telefono.show();
    }
    return test;
  }

  function verifyRadios() {
    var check = true;
    var error_radios = $('#error_radios');
    $('#reg-form input:radio').each(function(){
      var name = $(this).attr("name");
      if($("input:radio[name="+name+"]:checked").length == 0){
        var exist = fail.some(function(e){
          return e === name;
        });
        if(!exist) {
          check = false;
        }
      }
    });
    if (check) {
      error_radios.html("");
      error_radios.hide();
    } else {
      error_radios.html("Selecciona una forma de pago");
      error_radios.show();
    }
    return check;
  }

  function verifyFormFields() {
    var email = $('#reg-form input#email').val();
    var test_email = verifyEmail(email);
    var test_email_empty = verifyEmailEmpty(email);
    var name = $('#reg-form input#name').val();
    var test_name = verifyName(name);
    var phone = $('#reg-form input#phone').val();
    var test_phone = verifyPhone(phone);
    var test_radios = verifyRadios();
    return (test_email && test_email_empty && test_name && test_phone && test_radios);
  }


});