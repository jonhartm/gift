function make_quiz_from_template(quiz, review=false) {
    window.console && console.log(quiz);
    for(var i=0; i<quiz.questions.length; i++) {
        question = quiz.questions[i];
        type = question.type;

        // should we add buttons in the template?
        question.review = review;

        console.log(type);
        if ( TEMPLATES[type] ) {
            template = TEMPLATES[type];
        } else {
            source  = $('#'+type).html();
            if ( source == undefined ) {
                window.console && console.log("Did not find template for question type="+type);
                continue;
            }
            template = Handlebars.compile(source);
            TEMPLATES[type] = template;
        }
        $('#quiz').append(template(question));
    }
}
