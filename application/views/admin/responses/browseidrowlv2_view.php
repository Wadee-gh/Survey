<style>
    body {
      font-size: 8pt;
      font-family: helvetica;
    }

    table {
      border-collapse: collapse;
      font-size: 8pt;
    }

    th, td {
      padding: 8px !important;
      /*margin: 10px;*/
      text-align: center;
      vertical-align: middle !important;
    }

    td.text-center, th.text-center {
      text-align: center;
    }

    label.hide {
      display: none;
    }

    div.label-text {
      display: inline;
    }
    div {
      margin: 0;
      padding: 0;
    }
    .form-control[disabled] {
        color: black;
        background-color: #f5f5f5;
        opacity: 1;
    }
    .question-item {
        border: none;
    }
    .signature-answer-preview {
        display: none;
        padding: 10px;
        position: absolute;
        left: 50%;
        top: 50%;
        margin-left: -50%;
        margin-top: -25vh;
        width: 50%;
    }

</style>
<div class="custom_lv2">
  <?php echo $output; ?>
</div>



