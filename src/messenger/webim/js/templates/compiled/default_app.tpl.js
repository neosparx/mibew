(function(){var a=Handlebars.template,b=Handlebars.templates=Handlebars.templates||{};b.default_control=a(function(a,b,c,d,e){c=c||a.helpers;var f="",g,h,i="function",j=this.escapeExpression;return f+="<strong>",h=c.title,h?g=h.call(b,{hash:{}}):(g=b.title,g=typeof g===i?g():g),f+=j(g)+"</strong>",f})})()