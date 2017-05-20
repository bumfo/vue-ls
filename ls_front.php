<?php

if (!defined('__PUB__')) {
  require 'common.php';
  die_code(404);
}

?>
<!DOCTYPE html>
<title>Ls</title>
<meta charset="utf-8">
<meta name=viewport content="width=device-width, initial-scale=1.0, maximum-scale=1">

<style>
  body {
    margin: 0;
  }
  body, input {
    font: 1rem/1.4 'open sans', sans-serif;
  }
  input {
    -webkit-appearance: none;
    margin: 0;
    padding: 0;
    background: none;
    border: none;
    border-radius: 0;
    outline: none;
  }
  main {
    padding: 1rem 2.5rem;
  }
  label {
    display: inline-block;
    text-align: right;
    width: 10rem;
    margin-left: -10rem;
  }
  label:after {
    content: '\00a0'
  }
  ul {
    margin: 0;
    padding: 1rem 0;
    list-style: none;
  }
  ul > li > a {
    text-decoration: none;
  }
  ul > li > a > span.matched {
    font-weight: 600;
  }

  main::after {
    content: 'a';
    font-weight: 600;
    position: absolute;
    text-indent: -9999px;
    /*opacity: 0;*/
  }
</style>
<link href="<?=__PUB__?>/s/opensans/opensans.css" rel="stylesheet">

<script src="<?=__PUB__?>/s/vue.js"></script>

<main>
  <label>Ls</label><input autocomplete="off" v-model="query" v-on:keypress="onkeypress" v-autofocus>
  <ul v-if="!query.length">
    <normal-entry v-for="entry in entries" v-bind:key="entry.href" v-bind:href="entry.href" v-bind:title="entry.title" v-on:click="entryClick"></normal-entry>
  </ul>
  <ul v-if="query.length">
    <matched-entry v-for="entry in matched" v-bind:key="entry.href" v-bind:href="entry.href" v-bind:hints="entry.hints" v-on:click="entryClick"></matched-entry>
  </ul>
</main>

<script>
  var api_uri = '<?=__PUB__?>/ls.php'
</script>

<script src="<?=__PUB__?>/ls.js"></script>
