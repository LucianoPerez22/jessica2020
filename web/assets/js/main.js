$(document).ready(function() {

      // Material
      $.material.init();

      // NAV
      // Panel primary -- Show / Hidde
      $('#js-nav-trigger').click(function () {
          $(this).toggleClass('active');
          $('#js-panel-primary').toggleClass('znv-open');
          $('.znv-page-overlay').toggleClass('znv-show');
      });
      $('#js-nav-trigger').click(function () {
        $(this).toggleClass('znv-nav-open');
      });
      // Panel secondary -- Show / Hidde
      var wasPanelOneClicked = false;
      var wasPanelTwoClicked = false;
      // Button One
      $('#js-panel-one').click(function () {
        clickPanelOne();
        if (wasPanelTwoClicked) {
          clickPanelTwo();
          wasPanelTwoClicked = !wasPanelTwoClicked;
        }
        wasPanelOneClicked = !wasPanelOneClicked;
      });
      // Button Two
      $('#js-panel-two').click(function () {
        clickPanelTwo();
        if (wasPanelOneClicked) {
          clickPanelOne();
          wasPanelOneClicked = !wasPanelOneClicked;
        }
        wasPanelTwoClicked = !wasPanelTwoClicked;
      });
      // Add class panel active
      function clickPanelOne() {
          $(this).toggleClass('active');
          $('#js-panel-secondary-1').toggleClass('znv-open');
          $('.znv-page-overlay').toggleClass('znv-show');
      }
      function clickPanelTwo() {
          $(this).toggleClass('active');
          $('#js-panel-secondary-2').toggleClass('znv-open');
          $('.znv-page-overlay').toggleClass('znv-show');
      }
      // links menú
      $('ul.dropdown-menu li.znv-active').each(function () {
        $(this).parent().closest('li#js-addClass-catActive').addClass('znv-active show');

// Nueva linea agregada para bootstrap 4
        $(this).parent().closest('li#js-addClass-catActive ul.dropdown-menu').addClass('show');
      });


      // COMPONENTS
      // Tooltips ---
      $("[rel='tooltip']").tooltip({
        trigger: 'hover',
      });
      // Modal ---
      $('#delete').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget) // Button that triggered the modal
        var recipient = button.data('whatever') // Extract info from data-* attributes
        // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
        // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
        var modal = $(this)
        modal.find('.js-name-user').text(recipient)
        modal.find('.modal-body input').val(recipient)
      });
      // Select2 ---
      $('.js-select2').select2({
        language: "es",
        placeholder: "",
        allowClear: true
      });
      $('.js-select2-placeholder').select2({
        placeholder: "-- Select --",
        allowClear: true
      });
      // Select2 Sin buscador --
      $('.js-select2-no-search').select2({
        placeholder: "-- Select --",
        minimumResultsForSearch: Infinity,
        allowClear: true
      });

      // Select 2 con banderas
      // Librería: http://flag-icon-css.lip.is/
      $(".js-select2-flags").select2({
          templateResult: formatState,
          templateSelection: formatState
      });
      function formatState (opt) {
          if (!opt.id) {
              return opt.text.toUpperCase();
          }
          var optimage = $(opt.element).attr('data-flag');
          console.log(optimage)
          if(!optimage){
             return opt.text.toUpperCase();
          } else {
              var $opt = $(
                 '<span><img class="flag-icon flag-icon" src="dist/images/flags/' + optimage + ".svg" + '" /> ' + opt.text.toUpperCase() + '</span>'
              );
              return $opt;
          }
      };

      // Datetimepicker ---
      $('.js-datetimepicker').datetimepicker();
      $('.js-datetimepicker').on("dp.change", setEmptyClass);
      // Datepicker ---
      $('.js-datepicker').datetimepicker({
        format: 'DD/MM/YYYY'
      });
      $('.js-datepicker').on("dp.change", setEmptyClass);
      // Timepicker ---
      $('.js-timepicker').datetimepicker({
        format: 'LT'
      });
      $('.js-timepicker').on("dp.change", setEmptyClass);
      function setEmptyClass(event){
          var $input = $(event.currentTarget);
          var $formGroup = $input.closest(".znv-form-group");

          if ($input.val() === "") {
              $formGroup.addClass("is-empty");
          }
          else {
              $formGroup.removeClass("is-empty");
          }
      }
    // TEXTAREA AUTOSIZE

    $('textarea[autosize]').each(function() {
  var $me  = $(this);
  $me.on('input change paste keydown', function() {
    $me.prop('rows', $me.val().split('\n').length);
  }).trigger('input');
})

      //UTILITY
      // Button Back ---
      $(".js-backLink").click(function(event) {
        event.preventDefault();
        history.back(1);
     });
      // Add class active ---
      $('.js-active').click(function () {
          $(this).toggleClass('znv-active');
      });

      // Popover - se encuentra en cada página

});
