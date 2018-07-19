$(document).on('click','.btn',function(){
    // get the button that was clicked on and split the id on the hyphen
    var clicked = $(this);

    // the first part of the button id is the question code
    var question_code = clicked.attr("id").split('-')[0];

    // is this the "mark correct" or "mark Incorrect" button?
    var mark_correct = (clicked.attr("id").split('-')[1] == "markcorrect");

    // get all of the buttons for this question
    var marking_buttons = $("#quiz").find($(".btn[id^='"+question_code+"']"));

    // just in case, make sure none of the buttons have the active class
    for (var i = 0; i < marking_buttons.length; i++) {
      marking_buttons.removeClass("active");
    }

    // add the active class back to the button that was clicked
    clicked.addClass("active");

    // get the marker icon to the left of the question
    var score_marker = $("#quiz").find($(".fa[id='"+question_code+"-scoremarker']"));

    // remove all classes from this marker
    score_marker.removeClass();

    if (mark_correct) {
        // add fa classes to make it a green check
        score_marker.addClass("fa fa-check text-success");
    } else {
        // add fa classes to make it a red x
        score_marker.addClass("fa fa-times text-danger");
    }
});
