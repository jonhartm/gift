$(document).on('click','.btn',function(){
    // get the list item for this question (the first li that is a parent of this button)
    var question = $(this).closest("li");

    // get the question code from the id of this li
    var question_code = question.attr("id");

    // get the marker icon to the left of the question
    var score_marker = question.find($(".score_marker"));

    // Was this button already clicked?
    if ($(this).hasClass("active")) {
        // remove the active class so neither button is shaded
        $(this).removeClass("active");
        // reset the score marker to the "i" icon
        score_marker.attr("class", "fa fa-info-circle text-info score_marker");
        // make sure we remove any hidden fields that might be there
        question.find($("input[type='hidden'][name$='-score']")).remove();
        return;
    }

    // is this the "mark correct" or "mark Incorrect" button?
    var mark_correct = ($(this).val() == "Mark Correct");

    // get all of the buttons for this question
    var marking_buttons = question.find($(".btn"));

    // just in case, make sure none of the buttons have the active class
    for (var i = 0; i < marking_buttons.length; i++) {
      marking_buttons.removeClass("active");
    }

    // add the active class back to the button that was clicked
    $(this).addClass("active");


    // remove all classes from this marker except the one identifying this as a score marker
    score_marker.attr("class", "score_marker");

    // set the score to a default value
    var score = false;

    if (mark_correct) {
        // add fa classes to make it a green check
        score_marker.addClass("fa fa-check text-success");
        score = 1;
    } else {
        // add fa classes to make it a red x
        score_marker.addClass("fa fa-times text-danger");
        score = 0;
    }

    // create a hidden input with the score for this question so it gets sent with the submit
    var result_id = question.find($("input[type='hidden'][name='result_id']")).val();
    if (result_id != "disabled") {
        var hidden_input = "<input type='hidden' name="+result_id+"|"+question_code+"-score value="+score+">";
    } else {
        var hidden_input = "<input type='hidden' name="+question_code+"-score value="+score+">";
    }

    // just in case, remove any hidden inputs for score this question has already
    question.find($("input[type='hidden'][name$='-score']")).remove();

    // add the hidden input
    question.append(hidden_input);
});
