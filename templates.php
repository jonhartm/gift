<script id="short_answer_question" type="text/x-handlebars-template">
  <li id="{{code}}">
    <p>
    {{#if scored}}{{#if correct}}
        <i class="fa fa-check text-success"></i>
    {{else}}
        <i class="fa fa-times text-danger"></i>
    {{/if}} {{/if}}
    {{{question}}}</p>
    <p><input type="text" name="{{code}}" value="{{value}}" size="80" {{#if review}}readonly{{/if}}/></p>
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
    <p><input type="checkbox" name="{{code}}" {{#if checked}}checked{{/if}} value="true" {{#if ../review}}onclick="return false;"{{/if}}/> {{text}}</p>
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
    <p><input type="radio" name="{{code}}" {{#if value_true}}checked{{/if}} value="T" {{#if review}}onclick="return false;"{{/if}}/> True
    <input type="radio" name="{{code}}" {{#if value_false}}checked{{/if}} value="F" {{#if review}}onclick="return false;"{{/if}}/> False
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
    <p><input type="radio" name="{{../code}}" {{#if checked}}checked{{/if}} value="{{code}}" {{#if ../review}}onclick="return false;"{{/if}}/> {{text}}</p>
    {{/each}}
    </div>
  </li>
</script>
<script id="essay_question" type="text/x-handlebars-template">
  <li id="{{code}}">
  <input type="hidden" name="result_id" value={{#if result_id}}{{result_id}}{{else}}not_used{{/if}} disabled>
    <p>
      {{#unless scored}}
          <i class="fa fa-info-circle text-info score_marker"></i>
      {{/unless}}

      {{#if scored}}{{#if correct}}
          <i class="fa fa-check text-success score_marker"></i>
          <input type='hidden' name="{{#if result_id}}{{result_id}}|{{/if}}{{code}}-score" value="{{value.score}}">
      {{else}}
          <i class="fa fa-times text-danger score_marker"></i>
          <input type='hidden' name="{{#if result_id}}{{result_id}}|{{/if}}{{code}}-score" value="{{value.score}}">
      {{/if}} {{/if}}

      {{{question}}}

      {{#if review}}
        <input type="button" class="btn btn-default {{#if scored}}{{#if correct}}active{{/if}}{{/if}}" id="{{code}}-markcorrect" value="Mark Correct">
        <input type="button" class="btn btn-default {{#if scored}}{{#unless correct}}active{{/unless}}{{/if}}" id="{{code}}-markincorrect" value="Mark Incorrect">
      {{/if}}

    </p>
    <p>
      <textarea rows="4" cols="80" name="{{#if result_id}}{{result_id}}|{{/if}}{{code}}" {{#if review}}readonly{{/if}}>{{value.submitted}}</textarea>
    </p>
  </li>
</script>
