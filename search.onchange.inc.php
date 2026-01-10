onchange="
  if (this.value.indexOf(String.fromCharCode(34)) === -1) {
    this.value = this.value
      .replace(/\s*-\s*/g,'-')
      .replace(/(?<![\p{L}\p{N}_])([\p{L}\p{N}]+(?:-[\p{L}\p{N}]+)+)(?![\p{L}\p{N}_])/gu,'&quot;$1&quot;');
  }
  this.value = this.value
    .replace(/[&#-]/g,' ')
    .replace(/\s+/g,' ')
    .trim();
"
