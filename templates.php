<script id="short_answer_question" type="text/x-handlebars-template">
  <li id="{{code}}">
    <p>
    {{#if scored}}{{#if correct}}
        <i class="fa fa-check text-success"></i>
    {{else}}
        <i class="fa fa-times text-danger"></i>
    {{/if}} {{/if}}
    {{{question}}}</p>
    <p><input type="text" name="{{code}}" value="{{value}}" size="80"/></p>
  </li>
</script>
<script id="multiple_answers_question" type="text/x-handlebars-template">
  <li id="{{code}}">
    <p>
    {{#if scored}}{{#if correct}}
        <i class="fa fa-check text-success"></i>
    {{else}}
        <i class="fa fa-times text-danger"></i>
    {{/if}} {{/if}}
    {{{question}}}</p>
    <div>
    {{#each answers}}
    <p><input type="checkbox" name="{{code}}" {{#if checked}}checked{{/if}} value="true"/> {{text}}</p>
    {{/each}}
    </div>
  </li>
</script>
<script id="true_false_question" type="text/x-handlebars-template">
  <li id="{{code}}">
    <p>
    {{#if scored}}{{#if correct}}
        <i class="fa fa-check text-success"></i>
    {{else}}
        <i class="fa fa-times text-danger"></i>
    {{/if}} {{/if}}
    {{{question}}}</p>
    <p><input type="radio" name="{{code}}" {{#if value_true}}checked{{/if}} value="T"/> True
    <input type="radio" name="{{code}}" {{#if value_false}}checked{{/if}} value="F"/> False
    </p>
  </li>
</script>
<script id="multiple_choice_question" type="text/x-handlebars-template">
  <li id="{{code}}">
    <p>
    {{#if scored}}{{#if correct}}
        <i class="fa fa-check text-success"></i>
    {{else}}
        <i class="fa fa-times text-danger"></i>
    {{/if}} {{/if}}
    {{{question}}}</p>
    <div>
    {{#each answers}}
    <p><input type="radio" name="{{../code}}" {{#if checked}}checked{{/if}} value="{{code}}"/> {{text}}</p>
    {{/each}}
    </div>
  </li>
</script>
<script id="essay_question" type="text/x-handlebars-template">
  <li id="{{code}}">
    <p>
      {{#unless scored}}
          <i class="fa fa-info-circle text-info score_marker"></i>
      {{/unless}}

      {{#if scored}}{{#if correct}}
          <i class="fa fa-check text-success score_marker"></i>
          <input type='hidden' name="{{code}}-score" value="{{value.score}}">
      {{else}}
          <i class="fa fa-times text-danger score_marker"></i>
          <input type='hidden' name="{{code}}-score" value="{{value.score}}">
      {{/if}} {{/if}}

      {{{question}}}

      {{#if review}}
        <input type="button" class="btn btn-default" id="{{code}}-markcorrect" value="Mark Correct">
        <input type="button" class="btn btn-default" id="{{code}}-markincorrect" value="Mark Incorrect">
      {{/if}}

    </p>
    <p>
      <textarea rows="4" cols="80" name="{{code}}">{{value.submitted}}</textarea>
    </p>
  </li>
</script>
