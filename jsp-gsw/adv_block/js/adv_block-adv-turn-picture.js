(function($){
  Drupal.behaviors.adv_block_adv_turn_picture = {
    attach: function (context, settings) {
      new Marquee(
      {
        MSClass : ["rc-list-wrapper","rc-list","slider","onmousedown"],
        Direction : 0,
        Step : 0.2,
        Width : 473,
        Height : 290,
        Timer : 20,
        DelayTime : 5000,
        WaitTime : 0,
        ScrollStep: 0,
        SwitchType: 2,
        AutoStart : true
      });
    }
  };
})(jQuery);
