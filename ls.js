!function() {
  'use strict'

  var xhr = (function() {
    function onreadystatechange(e) {
      if (this.readyState === 4) {
        if (this.status === 200) {
          this.done(this.responseText)
        } else {
          this.reject(this.status)
        }
      }
    }

    function xhr(uri) {
      var request = new XMLHttpRequest()
      request.open('get', uri)
      request.send()
      request.onreadystatechange = onreadystatechange
      return new Promise(function(done, reject) {
        request.done = done
        request.reject = reject
      })
    }

    return xhr
  }())

  var INF = 0x7fffffff

  function insertionDistance(stack, needle, matches, factorStart = 1, factorEnd = 1, full = false) {
    var m = stack.length
    var n = needle.length
    var k = 0
    var d = 0
    var j = 0
    for (var i = 0; i < m && k < n; ++i) {
      if (stack[i] === needle[k]) {
        if (matches) matches.push(i)
        if (j === 0) {
          d += factorStart * (i - j)
        } else {
          d += i - j
        }
        j = i
        ++k
      }
    }
    i = m
    d += factorEnd * (i - j)
    if (k === n || !full) {
      return d
    } else {
      return INF
    }
  }

  Vue.directive('autofocus', {
    inserted: function(el) {
      if (!('touchstart' in window)) {
        el.focus()
      }
    }
  })

  var app = new Vue({
    components: {
      normalEntry: {
        template: '<li><a v-bind:href="href" v-on:click.stop="onclick">{{ title }}</a></li>',
        props: {
          href: '',
          title: '',
        },
        methods: {
          onclick(e) {
            this.$emit('click', {target: this, preventDefault() {
              e.preventDefault()
            }})
          }
        }
      },
      matchedEntry: {
        template: '<li><a v-bind:href="href" v-on:click.stop="onclick"><span v-for="hint in hints" v-bind:class="{ matched: hint.matched }">{{ hint.text }}</span></a></li>',
        props: {
          href: '',
          hints: {
            type: Array
          },
        },
        methods: {
          onclick(e) {
            this.$emit('click', {target: this, preventDefault() {
              e.preventDefault()
            }})
          }
        }
      }
    },
    el: 'main',
    data: {
      path: '',
      query: '',
      entries: [{ title: '.', href: '.', }],
      inited: false,
      isPopState: false,
    },
    mounted() {
      if (!history.state) {
        this.path = location.pathname
      } else {
        this.fetchState(history.state)
        this.refresh()
      }
    },
    watch: {
      query() {
        history.state.query = this.query
        history.replaceState(history.state, this.title)
      },
      path() {
        if (this.isPopState) {
          document.title = this.title
          this.isPopState = false
        } else {
          this.queryUpdate().then(() => {
            if (this.inited) {
              history.pushState(this.state, this.title, this.path)
            } else {
              this.inited = true
              history.replaceState(this.state, this.title, this.path)
            }
            document.title = this.title
            this.query = ''
          }).catch((err) => {
            if (err !== 302) {
              console.error(err)
            }
          })
        }
      }
    },
    methods: {
      fetchState(state) {
        this.isPopState = true
        this.path = state.path
        this.query = state.query
        this.entries = state.entries
      },
      refresh() {
        this.queryUpdate().then(() => {
          this.inited = true
          history.replaceState(this.state, this.title, this.path)
        })
      },
      navigate(href) {
        if (href[href.length - 1] === '/') {
          this.path = href
        } else {
          location.href = href
        }
      },
      queryUpdate() {
        return xhr(api_uri + '?d=' + this.path).then((text) => {
          this.entries = JSON.parse(text)
        }).catch((err) => {
          switch (err) {
          case 302:
            location.href = this.path
            throw err
            break
          case 403:
          case 404:
            var path = this.path
            path = path.substr(0, path.length - 1)
            path = path.substr(0, path.lastIndexOf('/') + 1)
            this.entries = [{ title: '..', href: path, }]
            this.query = ''
            throw err
            break
          default:
            throw err
            break
          }
        })
      },
      onkeypress(e) {
        if (e.key === 'Enter') {
          e.preventDefault()
          if (this.query === '') {
          } else {
            if (this.matched.length) {
              this.navigate(this.matched[0].href)
            }
          }
        }
      },
      entryClick(e) {
        e.preventDefault()
        this.navigate(e.target.href)
      }
    },
    computed: {
      state() {
        return {
          path: this.path,
          query: this.query,
          entries: this.entries,
        }
      },
      title() {
        return 'Ls ' + this.path
      },
      matched() {
        return this.entries.map(entry => {
          var matches = []
          var d = insertionDistance(entry.title, this.query, matches, 100, 0, true)
          var hints = []
          var j = 0
          var l = 0
          for (var k = 0; k < matches.length; ++k) {
            var i = matches[k]

            if (i === l) {
              l = i + 1
            } else {
              if (j < l) hints.push({ text: entry.title.substring(j, l), matched: true })
              hints.push({ text: entry.title.substring(l, i), matched: false })
              j = i
              l = i + 1
            }
          }
          i = entry.title.length
          if (j < l) hints.push({ text: entry.title.substring(j, l), matched: true })
          hints.push({ text: entry.title.substring(l, i), matched: false })
          return { d, href: entry.href, hints }
        }).filter(a => a.d !== INF).sort((a, b) => a.d - b.d)
      }
    },
  })

  window.addEventListener('popstate', function(e) {
    app.fetchState(e.state)
  })
}.call(this)
