// Bug IE
if(!('remove' in Element.prototype)) {
    Element.prototype.remove = function() {
        if (this.parentNode){
            this.parentNode.removeChild(this);
        }
    };
}
String.prototype.replaceAll = function(str1, str2, ignore) 
{
    return this.replace(new RegExp(str1.replace(/([\/\,\!\\\^\$\{\}\[\]\(\)\.\*\+\?\|\<\>\-\&])/g,"\\$&"),(ignore?"gi":"g")),(typeof(str2)=="string")?str2.replace(/\$/g,"$$$$"):str2);
};
String.prototype.trimMask = function(mask) {
	let s = this.trim();
    while (~mask.indexOf(s[0])) {
        s = s.slice(1);
    }
    while (~mask.indexOf(s[s.length - 1])) {
        s = s.slice(0, -1);
    }
	s = s.trim();
	s = s.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, ''); //NOSONAR
    return s;
}
String.prototype.countSubstring = function(subText)
{
	let arr = this.split(subText);
	return arr.length-1;
}
String.prototype.escapeHTML = function(repAll) {
	if(typeof repAll == 'undefined')
	{
		return this
			.replace(/<br \/>/g, "\n")
			.replace(/&/g, "&amp;")
			.replace(/</g, "&lt;")
			.replace(/>/g, "&gt;")
			.replace(/"/g, "&quot;")
			.replace(/'/g, "&#039;")
			.replace(/\n/g, "<br />");
	}
	else
	{
		return this
			.replace(/<br \/>/g, "\n")
			.replace(/&/g, "&amp;")
			.replace(/</g, "&lt;")
			.replace(/>/g, "&gt;")
			.replace(/"/g, "&quot;")
			.replace(/'/g, "&#039;")
			.replace(/\n/g, "&lt;br /&gt;");
	}
}
String.prototype.restoreBR = function() {
	return this
		.replace(/&lt;br \/&gt;/g, " <br />")
}
String.prototype.escapeHTMLEntities = function() {
	return escapeHtmlEntities(this);
}
if(typeof escapeHtmlEntities == 'undefined') {
	escapeHtmlEntities = function (text) {
		return text.replace(/[\u00A0-\u2666<>\&]/g, function(c) {
			return '&' + 
			(escapeHtmlEntities.entityTable[c.charCodeAt(0)] || '#'+c.charCodeAt(0)) + ';';
		});
	};

	// all HTML4 entities as defined here: http://www.w3.org/TR/html4/sgml/entities.html
	// added: amp, lt, gt, quot and apos
	escapeHtmlEntities.entityTable = {
		34 : 'quot', 
		38 : 'amp', 
		39 : 'apos', 
		60 : 'lt', 
		62 : 'gt', 
		160 : 'nbsp', 
		161 : 'iexcl', 
		162 : 'cent', 
		163 : 'pound', 
		164 : 'curren', 
		165 : 'yen', 
		166 : 'brvbar', 
		167 : 'sect', 
		168 : 'uml', 
		169 : 'copy', 
		170 : 'ordf', 
		171 : 'laquo', 
		172 : 'not', 
		173 : 'shy', 
		174 : 'reg', 
		175 : 'macr', 
		176 : 'deg', 
		177 : 'plusmn', 
		178 : 'sup2', 
		179 : 'sup3', 
		180 : 'acute', 
		181 : 'micro', 
		182 : 'para', 
		183 : 'middot', 
		184 : 'cedil', 
		185 : 'sup1', 
		186 : 'ordm', 
		187 : 'raquo', 
		188 : 'frac14', 
		189 : 'frac12', 
		190 : 'frac34', 
		191 : 'iquest', 
		192 : 'Agrave', 
		193 : 'Aacute', 
		194 : 'Acirc', 
		195 : 'Atilde', 
		196 : 'Auml', 
		197 : 'Aring', 
		198 : 'AElig', 
		199 : 'Ccedil', 
		200 : 'Egrave', 
		201 : 'Eacute', 
		202 : 'Ecirc', 
		203 : 'Euml', 
		204 : 'Igrave', 
		205 : 'Iacute', 
		206 : 'Icirc', 
		207 : 'Iuml', 
		208 : 'ETH', 
		209 : 'Ntilde', 
		210 : 'Ograve', 
		211 : 'Oacute', 
		212 : 'Ocirc', 
		213 : 'Otilde', 
		214 : 'Ouml', 
		215 : 'times', 
		216 : 'Oslash', 
		217 : 'Ugrave', 
		218 : 'Uacute', 
		219 : 'Ucirc', 
		220 : 'Uuml', 
		221 : 'Yacute', 
		222 : 'THORN', 
		223 : 'szlig', 
		224 : 'agrave', 
		225 : 'aacute', 
		226 : 'acirc', 
		227 : 'atilde', 
		228 : 'auml', 
		229 : 'aring', 
		230 : 'aelig', 
		231 : 'ccedil', 
		232 : 'egrave', 
		233 : 'eacute', 
		234 : 'ecirc', 
		235 : 'euml', 
		236 : 'igrave', 
		237 : 'iacute', 
		238 : 'icirc', 
		239 : 'iuml', 
		240 : 'eth', 
		241 : 'ntilde', 
		242 : 'ograve', 
		243 : 'oacute', 
		244 : 'ocirc', 
		245 : 'otilde', 
		246 : 'ouml', 
		247 : 'divide', 
		248 : 'oslash', 
		249 : 'ugrave', 
		250 : 'uacute', 
		251 : 'ucirc', 
		252 : 'uuml', 
		253 : 'yacute', 
		254 : 'thorn', 
		255 : 'yuml', 
		402 : 'fnof', 
		913 : 'Alpha', 
		914 : 'Beta', 
		915 : 'Gamma', 
		916 : 'Delta', 
		917 : 'Epsilon', 
		918 : 'Zeta', 
		919 : 'Eta', 
		920 : 'Theta', 
		921 : 'Iota', 
		922 : 'Kappa', 
		923 : 'Lambda', 
		924 : 'Mu', 
		925 : 'Nu', 
		926 : 'Xi', 
		927 : 'Omicron', 
		928 : 'Pi', 
		929 : 'Rho', 
		931 : 'Sigma', 
		932 : 'Tau', 
		933 : 'Upsilon', 
		934 : 'Phi', 
		935 : 'Chi', 
		936 : 'Psi', 
		937 : 'Omega', 
		945 : 'alpha', 
		946 : 'beta', 
		947 : 'gamma', 
		948 : 'delta', 
		949 : 'epsilon', 
		950 : 'zeta', 
		951 : 'eta', 
		952 : 'theta', 
		953 : 'iota', 
		954 : 'kappa', 
		955 : 'lambda', 
		956 : 'mu', 
		957 : 'nu', 
		958 : 'xi', 
		959 : 'omicron', 
		960 : 'pi', 
		961 : 'rho', 
		962 : 'sigmaf', 
		963 : 'sigma', 
		964 : 'tau', 
		965 : 'upsilon', 
		966 : 'phi', 
		967 : 'chi', 
		968 : 'psi', 
		969 : 'omega', 
		977 : 'thetasym', 
		978 : 'upsih', 
		982 : 'piv', 
		8226 : 'bull', 
		8230 : 'hellip', 
		8242 : 'prime', 
		8243 : 'Prime', 
		8254 : 'oline', 
		8260 : 'frasl', 
		8472 : 'weierp', 
		8465 : 'image', 
		8476 : 'real', 
		8482 : 'trade', 
		8501 : 'alefsym', 
		8592 : 'larr', 
		8593 : 'uarr', 
		8594 : 'rarr', 
		8595 : 'darr', 
		8596 : 'harr', 
		8629 : 'crarr', 
		8656 : 'lArr', 
		8657 : 'uArr', 
		8658 : 'rArr', 
		8659 : 'dArr', 
		8660 : 'hArr', 
		8704 : 'forall', 
		8706 : 'part', 
		8707 : 'exist', 
		8709 : 'empty', 
		8711 : 'nabla', 
		8712 : 'isin', 
		8713 : 'notin', 
		8715 : 'ni', 
		8719 : 'prod', 
		8721 : 'sum', 
		8722 : 'minus', 
		8727 : 'lowast', 
		8730 : 'radic', 
		8733 : 'prop', 
		8734 : 'infin', 
		8736 : 'ang', 
		8743 : 'and', 
		8744 : 'or', 
		8745 : 'cap', 
		8746 : 'cup', 
		8747 : 'int', 
		8756 : 'there4', 
		8764 : 'sim', 
		8773 : 'cong', 
		8776 : 'asymp', 
		8800 : 'ne', 
		8801 : 'equiv', 
		8804 : 'le', 
		8805 : 'ge', 
		8834 : 'sub', 
		8835 : 'sup', 
		8836 : 'nsub', 
		8838 : 'sube', 
		8839 : 'supe', 
		8853 : 'oplus', 
		8855 : 'otimes', 
		8869 : 'perp', 
		8901 : 'sdot', 
		8968 : 'lceil', 
		8969 : 'rceil', 
		8970 : 'lfloor', 
		8971 : 'rfloor', 
		9001 : 'lang', 
		9002 : 'rang', 
		9674 : 'loz', 
		9824 : 'spades', 
		9827 : 'clubs', 
		9829 : 'hearts', 
		9830 : 'diams', 
		338 : 'OElig', 
		339 : 'oelig', 
		352 : 'Scaron', 
		353 : 'scaron', 
		376 : 'Yuml', 
		710 : 'circ', 
		732 : 'tilde', 
		8194 : 'ensp', 
		8195 : 'emsp', 
		8201 : 'thinsp', 
		8204 : 'zwnj', 
		8205 : 'zwj', 
		8206 : 'lrm', 
		8207 : 'rlm', 
		8211 : 'ndash', 
		8212 : 'mdash', 
		8216 : 'lsquo', 
		8217 : 'rsquo', 
		8218 : 'sbquo', 
		8220 : 'ldquo', 
		8221 : 'rdquo', 
		8222 : 'bdquo', 
		8224 : 'dagger', 
		8225 : 'Dagger', 
		8240 : 'permil', 
		8249 : 'lsaquo', 
		8250 : 'rsaquo', 
		8364 : 'euro'
	};
}


String.prototype.splitWithLimit = function(separator, limit) {
	let value = this;
	value = value.toString();
	let pattern;
	let startIndex;
	let m;
	let parts = [];

    if(!limit) {
        return value.split(separator);
    }

    if(separator instanceof RegExp) {
        pattern = new RegExp(separator.source, 'g' + (separator.ignoreCase ? 'i' : '') + (separator.multiline ? 'm' : ''));
    } else {
        pattern = new RegExp(separator.replace(/([.*+?^${}()|\[\]\/\\])/g, '\\$1'), 'g');
    }
    do {
        startIndex = pattern.lastIndex;
		m = pattern.exec(value);
        if(m) 
		{
            parts.push(value.length < (m.index - startIndex) ? value:   value.substring(startIndex, m.index - startIndex));
        }
    } 
	while(m && parts.length < limit - 1);
    parts.push(value.substring(pattern.lastIndex));

    return parts;
}


function getNumberingType(opt1, opt2)
{
	let o1 = opt1.split('.')[0].trim();
	let o2 = opt2.split('.')[0].trim();
	let numbering;
	if(o1 == 'A' && o2 == 'B')
	{
		numbering = 'upper-alpha';
	}
	else if(o1 == 'a' && o2 == 'b')
	{
		numbering = 'lower-alpha';
	}
	else if(o1 == 'I' && o2 == 'II')
	{
		numbering = 'upper-roman';
	}
	else if(o1 == 'i' && o2 == 'ii')
	{
		numbering = 'lower-roman';
	}
	else if(o1 == '1' && o2 == '2')
	{
		numbering = 'decimal';
	}
	else if(o1 == '01' && o2 == '02')
	{
		numbering = 'decimal-leading-zero';
	}
	else
	{
		numbering = 'upper-alpha';
	}
	return numbering;
}
let numberingList = {
	'upper-alpha':['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'],
	'lower-alpha':['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j'],
	'upper-roman':['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'],
	'lower-roman':['i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'vii', 'ix', 'x'],
	'decimal':['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
	'decimal-leading-zero':['01', '02', '03', '04', '05', '06', '07', '08', '09', '10']
};
function optionMatch(opt, numbering)
{
	let num = numberingList[numbering];
	if(typeof num == 'undefined')
	{
		return -1;
	}
	return num.indexOf(opt); 
}
function parseQuestion(input) //NOSONAR
{
	let i;
	let k;
	let input1, input2, question, options = [];
	let numbering = '';
	let opt = '';
	let tmp = [];
	
	tmp = input.split("\n");
	for(i in tmp)
	{
		tmp[i] = tmp[i].trimMask(" \r\n\t ");
	}
	input = tmp.join("\n");
	tmp = [];
	input1 = input;
	input2 = input1.replaceAll("\\\\\n", "<br />");
	let lines = input2.split("\n");
	if(lines.length >= 3)
	{
		question = lines[0];
		for(i in lines)
		{
			lines[i] = lines[i].trimMask(" \r\n\t ");
		}
		i = 1;
		do{
			numbering = getNumberingType(lines[i], lines[i+1]);
			i++;
			if(i >= lines.length - 2)
			{
				break;
			}
		}
		while(numbering == '');
		
		for(i = 1, k = -1; i<lines.length-1; i++)
		{
			if(lines[i].indexOf('.') > -1)
			{
				tmp = lines[i].splitWithLimit('.', 2);
				opt = tmp[0].trimMask(" \r\n\t ");
				if(optionMatch(opt, numbering) > -1)
				{
					options.push({'text':tmp[1].trimMask(" \r\n\t "), 'score':0});
					k++;
				}
				else
				{
					if(k == -1)
					{
						question += '<br />'+lines[i];
					}
					else
					{
						options[k].text += '<br />'+lines[i];
					}
				}
			}
			else
			{
				if(k == -1)
				{
					question += '<br />'+lines[i];
				}
				else
				{
					options[k].text += '<br />'+lines[i];
				}
			}
		}
		if(lines.length > 3)
		{
			let lastIsAnswer = true;
			if(lines[lines.length-1].countSubstring(":") == lines[lines.length-1].countSubstring("\\\\:"))
			{
				lastIsAnswer = false;
			}
			if(lines[lines.length-1].indexOf(':') > -1 && lastIsAnswer)
			{
				lines[lines.length-1] = lines[lines.length-1].replaceAll("\t", " ");
				tmp = lines[lines.length-1].splitWithLimit(':', 2);
				opt = tmp[1].trimMask(" \r\n\t ");
				opt = opt.split(" ")[0].trimMask(" \r\n\t ");
				let answerIndex = optionMatch(opt, numbering);
				if(answerIndex > -1 && answerIndex < options.length)
				{
					options[answerIndex]['score'] = 1;
				}
				else
				{
					tmp = lines[lines.length-1].splitWithLimit('.', 2);
					opt = tmp[0].trimMask(" \r\n\t ");
					if(optionMatch(opt, numbering) > -1)
					{
						options.push({'text':tmp[1].trimMask(" \r\n\t "), 'score':0});
					}
				}
			}
			else
			{
				if(lines[lines.length-1].indexOf('.') > -1)
				{
					tmp = lines[lines.length-1].splitWithLimit('.', 2);
					opt = tmp[0].trimMask(" \r\n\t ");
					if(optionMatch(opt, numbering) > -1)
					{
						options.push({'text':tmp[1].trimMask(" \r\n\t "), 'score':0});
					}
				}
			}
		}
	}
	else
	{
		options = [];
		numbering = 'upper-alpha';
		question = lines[0];
	}
	question.replaceAll("\\\\:", ":");
	for(i in options)
	{
		options[i].text = options[i].text.replaceAll("\\\\:", ":");
	}
	return {question:question, options:options, numbering:numbering};
	
}
function buldOptionHTML(question, parseImg, baseIMGURL)
{
	parseImg = parseImg || false;
	baseIMGURL = baseIMGURL || '';
	let i;
	let j;
	let html = '';
	let score = '';
	
	if(typeof question.numbering == 'undefined')
	{
		question.numbering = 'upper-alpha';
	}	
	html += '\r\n\t<ol class="option-ol" start="'+numberingList[question.numbering][0]+'" style="list-style-type:'+question.numbering+'">';
	for(i in question.options)
	{
		j = question.options[i];
		if(j.score > 0)
		{
			score = '<span class="score"></span>';
		}
		else
		{
			score = '';
		}
		html += '\r\n\t\t<li class="option-li">'+score+'<span>'+j.text.escapeHTMLEntities().restoreBR().addImage(parseImg, baseIMGURL)+'</span></li>';
	}
	html += '\r\n\t</ol>';
	return html;
}
function buildQuestionHTML(inObj, parseImg, baseIMGURL)
{
	parseImg = parseImg || false;
	baseIMGURL = baseIMGURL || '';
	let i;
	let html = '';
	if(inObj != null)
	{
		if(inObj.length > 0)
		{
			html += '\r\n<ol class="question-ol">';
			for(i in inObj)
			{
				html += '\r\n\t<li>'+'<span>'+inObj[i].question.escapeHTMLEntities().restoreBR().addImage(parseImg, baseIMGURL)+'</span>'+
				buldOptionHTML(inObj[i], parseImg, baseIMGURL)
				+'\r\n\t</li>';
			}
			html += '\r\n</ol>';
		}
	}
	return html;
}
function buildQuestion(input)
{
	let input1, input2;
	let question;
	input1 = input.toString();
	input2 = input1.replaceAll("\r\n", "\n");
	while(input2.indexOf("\n\n\n") > -1)
	{
		input2 = input2.replaceAll("\n\n\n", "\n\n");
	}
	
	input2 = input2.trimMask(" \r\n\t ");
	if(input2.length > 0)
	{
		let questions = input2.split("\n\n");
		
		let retObj = [];
		for(let i in questions)
		{
			question = questions[i].trimMask(" \r\n\t ");
			if(question != '')
			{
				retObj.push(parseQuestion(question));
			}
		}
		return retObj;
	}
	return null;
}

function getYouTubeParams(url)
{
	url = url.toString();
	let videoid = url.match(/(?:https?:\/{2})?(?:w{3}\.)?youtu(?:be)?\.(?:com|be)(?:\/watch\?v=|\/)([^\s&]+)/);
	if(videoid != null) {
		return {video_id:videoid[1], url:url};
	} else { 
		return null;
	}
}
let verticalAlign = ['baseline','top','bottom','middle','text-top','text-bottom'];
String.prototype.addImage = function(image, base){
	let str = this;
	base = base || '';
	if(!image)
	{
		return this;
	}
	else
	{
		let i;
		let j;
		let k;
		let l;
		let m;
		let n;
		let txt;
		let file;
		let imghtml;
		let pos;
		let base2;
		
		// begin image
		let arrayX = str.match(/(?:^|)img:([a-zA-Z\S]+)/gi); //NOSONAR
		for(i in arrayX)
		{
			txt = arrayX[i].trimMask(" \r\n\t ");
			pos = txt.indexOf('img:');
			file = txt.substr(pos+4);
			k = '';
			if(file.indexOf('#') > -1)
			{
				j = file.split('#');
				file = j[0];
				if($.inArray(j[1], verticalAlign) > -1)
				{
					k += ' style="vertical-align:'+j[1]+'"';
				}
				if(j.length > 2)
				{
					l = j[2].split(',');
					m = parseInt(l[0] || '0');
					n = parseInt(l[1] || '0');
					if(m>0)
					{
						k += ' width="'+m+'"';
					}
					if(n>0)
					{
						k += ' height="'+n+'"';
					}
				}
			}
			
			if(file.indexOf("://") != -1)
			{
				base2 = '';
			}
			else
			{
				base2 = base;
			}
			imghtml = '<img src="'+base2+file+'"'+k+'>';
			str = str.replace(txt, imghtml);
		}
		// end image
		
		// begin audio
		arrayX = str.match(/(?:^|)audio:([a-zA-Z\S]+)/gi); //NOSONAR
		for(i in arrayX)
		{
			txt = arrayX[i].trimMask(" \r\n\t ");
			pos = txt.indexOf('audio:');
			file = txt.substr(pos+6);
			k = '';
			if(file.indexOf('#') > -1)
			{
				j = file.split('#');
				file = j[0];
				if($.inArray(j[1], verticalAlign) > -1)
				{
					k += ' style="vertical-align:'+j[1]+'"';
				}
				if(j.length > 2)
				{
					l = j[2].split(',');
					m = parseInt(l[0] || '0');
					n = parseInt(l[1] || '0');
					if(m>0)
					{
						k += ' width="'+m+'"';
					}
					else
					{
						k += ' width="300"';
					}
					if(n>0)
					{
						k += ' height="'+n+'"';
					}
					else
					{
						k += ' height="50"';
					}
				}
				else
				{
					k += ' width="300" height="50"';
				}
			}
			else
			{
				k += ' width="300" height="40"';
			}
			
			if(file.indexOf("://") != -1)
			{
				base2 = '';
			}
			else
			{
				base2 = base;
			}
			if(file.indexOf("://") != -1)
			{
				base2 = '';
			}
			else
			{
				base2 = base;
			}
			imghtml = '<audio src="'+base2+file+'"'+k+' controls></audio>';
			str = str.replace(txt, imghtml);
		}
		// end audio


		// begin video
		arrayX = str.match(/(?:^|)video:([a-zA-Z\S]+)/gi); //NOSONAR
		for(i in arrayX)
		{
			txt = arrayX[i].trimMask(" \r\n\t ");
			pos = txt.indexOf('video:');
			file = txt.substr(pos+6);
			k = '';
			if(file.indexOf('#') > -1)
			{
				j = file.split('#');
				file = j[0];
				if($.inArray(j[1], verticalAlign) > -1)
				{
					k += ' style="vertical-align:'+j[1]+'"';
				}
				if(j.length > 2)
				{
					l = j[2].split(',');
					m = parseInt(l[0] || '0');
					n = parseInt(l[1] || '0');
					if(m>0)
					{
						k += ' width="'+m+'"';
					}
					else
					{
						k += ' width="500"';
					}
					if(n>0)
					{
						k += ' height="'+n+'"';
					}
					else
					{
						k += ' height="280"';
					}
				}
				else
				{
					k += ' width="500" height="280"';
				}
			}
			else
			{
				k += ' width="500" height="280"';
			}
			
			if(file.indexOf("://") != -1)
			{
				base2 = '';
			}
			else
			{
				base2 = base;
			}
			if(file.indexOf("://") != -1)
			{
				base2 = '';
			}
			else
			{
				base2 = base;
			}
			imghtml = '<video src="'+base2+file+'"'+k+' controls></video>';
			str = str.replace(txt, imghtml);
		}
		// end video
		// begin iframe
		arrayX = str.match(/(?:^|)iframe:([a-zA-Z\S]+)/gi); //NOSONAR
		for(i in arrayX)
		{
			txt = arrayX[i].trimMask(" \r\n\t ");
			pos = txt.indexOf('iframe:');
			file = txt.substr(pos+7);
			k = '';
			if(file.indexOf('#') > -1)
			{
				j = file.split('#');
				file = j[0];
				if($.inArray(j[1], verticalAlign) > -1)
				{
					k += ' style="vertical-align:'+j[1]+'"';
				}
				if(j.length > 2)
				{
					l = j[2].split(',');
					m = parseInt(l[0] || '0');
					n = parseInt(l[1] || '0');
					if(m>0)
					{
						k += ' width="'+m+'"';
					}
					else
					{
						k += ' width="500"';
					}
					if(n>0)
					{
						k += ' height="'+n+'"';
					}
					else
					{
						k += ' height="280"';
					}
				}
				else
				{
					k += ' width="500" height="280"';
				}
			}
			else
			{
				k += ' width="500" height="280"';
			}
			
			if(file.indexOf("://") != -1)
			{
				base2 = '';
			}
			else
			{
				base2 = base;
			}
			if(file.indexOf("://") != -1)
			{
				base2 = '';
			}
			else
			{
				base2 = base;
			}
			imghtml = '<iframe src="'+base2+file+'"'+k+' allowfullscreen="allowfullscreen" frameborder="0"></iframe>';
			str = str.replace(txt, imghtml);
		}
		// end video
		
		// begin youtube
		arrayX = str.match(/(?:^|)youtube:([a-zA-Z\S]+)/gi); //NOSONAR
		for(i in arrayX)
		{
			txt = arrayX[i].trimMask(" \r\n\t ");
			pos = txt.indexOf('img:');
			file = txt.substr(pos+4);
			k = '';
			if(file.indexOf('#') > -1)
			{
				j = file.split('#');
				file = j[0];
				if($.inArray(j[1], verticalAlign) > -1)
				{
					k += ' style="vertical-align:'+j[1]+'"';
				}
			}
			
			if(file.indexOf("://") != -1)
			{
				base2 = '';
			}
			else
			{
				base2 = base;
			}
			let style_element = k;
			let yt = getYouTubeParams(file);
			let time = 0;
			let video_id = yt['video_id'];
			imghtml = '<iframe type="text/html" marginwidth="0" marginheight="0" scrolling="no" src="https://www.youtube.com/embed/'+video_id+'?html5=1&playsinline=1&allowfullscreen=true&rel=0&version=3&autoplay=0&start='+time+'" allowfullscreen="" height="281" width="500"'+style_element+' frameborder="0"></iframe>';
			str = str.replace(txt, imghtml);
		}
		// end youtube
		



		// begin equation
		arrayX = str.split('$$');
		let txtTrimmed = '';
		for(i = 1; i<arrayX.length; i+=2)
		{
			txt = arrayX[i];
			txtTrimmed = txt.trimMask(" \r\n\t ");
			if(txtTrimmed.length)
			{
				imghtml = '<img src="../cgi-bin/equgen.cgi?'+txt+'" style="vertical-align:middle" data-latex="'+txt+'" alt="'+txt+'">';
				str = str.replace('$$'+txt+'$$', imghtml);
			}
			else
			{
				str = str.replace('$$'+txt+'$$', '');
			}
		}
		// end equation
		
		
		
		
	}
	return str;
}
function getInputSelection(el) {
    let start = 0, end = 0, normalizedValue, range,
        textInputRange, len, endRange;

    if (typeof el.selectionStart == "number" && typeof el.selectionEnd == "number") {
        start = el.selectionStart;
        end = el.selectionEnd;
    } else {
        range = document.selection.createRange();

        if (range && range.parentElement() == el) {
            len = el.value.length;
            normalizedValue = el.value.replace(/\r\n/g, "\n");

            // Create a working TextRange that lives only in the input
            textInputRange = el.createTextRange();
            textInputRange.moveToBookmark(range.getBookmark());

            // Check if the start and end of the selection are at the very end
            // of the input, since moveStart/moveEnd doesn't return what we want
            // in those cases
            endRange = el.createTextRange();
            endRange.collapse(false);

            if (textInputRange.compareEndPoints("StartToEnd", endRange) > -1) {
                start = end = len;
            } else {
                start = -textInputRange.moveStart("character", -len);
                start += normalizedValue.slice(0, start).split("\n").length - 1;

                if (textInputRange.compareEndPoints("EndToEnd", endRange) > -1) {
                    end = len;
                } else {
                    end = -textInputRange.moveEnd("character", -len);
                    end += normalizedValue.slice(0, end).split("\n").length - 1;
                }
            }
        }
    }

    return {
        start: start,
        end: end
    };
}

function noAnswer(questions)
{
	let i;
	let j;
	let k;
	let m = [];
	let score;
	if(questions != null)
	{
		for(i in questions)
		{
			j = questions[i].options;
			score = 0;
			for(k in j)
			{
				score += j[k].score;
			}
			if(score == 0)
			{
				m.push(parseInt(i)+1);
			}
		}
	}
	return m;
}
function inputChanged(e){
	let elemInput = e.target;
	let elemOutput = document.getElementById('preview2');
	let elemStatus = document.getElementById('status');
	renderQuestion(elemInput, elemOutput, elemStatus);
}
function renderQuestion(elemInput, elemOutput, elemStatus)
{
	let input = elemInput.value;
	let out = elemOutput;
	
	let questions = buildQuestion(input);
	out.innerHTML = buildQuestionHTML(questions, parseImg, baseIMGURL);
	$('#preview2 > ol > li:first-child').attr('value', startFrom);
	let noAnswerQuestion = noAnswer(questions);
	if(questions == null)
	{
		elemStatus.innerHTML = '';
	}
	else
	{
		let nQuestion = questions.length;
		if(modContinue)
		{
			let i;
			for(i = 0; i < noAnswerQuestion.length; i++)
			{
				noAnswerQuestion[i] += startFrom-1;
			}
			nQuestion += startFrom-1;
		}
		let status = '';
		status = 'Jumlah soal: '+nQuestion;
		if(noAnswerQuestion.length)
		{
			status += ' | Tanpa jawaban: Nomor '+noAnswerQuestion.join(', ');
		}
		elemStatus.innerHTML = status;
	}
}
let contentModified = false;
let currentFileName = '';
let fileNameList = [];

function saveFileData(name, value)
{
	let fls = window.localStorage.getItem('filelist') || '[]';
	fileNameList =  eval(fls);
	if(fileNameList.indexOf(currentFileName) == -1)
	{
		fileNameList.push(currentFileName);
	}
	window.localStorage.setItem('filelist', JSON.stringify(fileNameList));
	window.localStorage.setItem('data_'+name, value);
	contentModified = false;
}
function loadFileData(name)
{
	let value = window.localStorage.getItem('data_'+name) || '';
	document.getElementById('input').value = value;
	document.getElementById('input').focus();
	contentModified = false;
}
function writeFile()
{
	let filename = document.getElementById('filename').value;
	filename = filename.trim();
	filename = filename.replace(/[^0-9A-Za-z_\-\(\)]/gi, ' '); //NOSONAR
	filename = filename.replace(/ {1,}/g," ").trim(); //NOSONAR
	if(filename == '')
	{
		alert('Silakan masukkan nama file');
		document.getElementById('filename').select();
	}
	else
	{
		currentFileName = filename;
		let value = document.getElementById('input').value;
		saveFileData(currentFileName, value);
		closeDialogFile();
	}
}
function selectFileList(name)
{
	document.getElementById('filename').value = name;
}
function showEquationDialog()
{
	let html = ''+
	'<div class="file-dialog" style="width:800px">\r\n'+
	'	<div class="file-dialog-title"><a class=\"file-dialog-close-icon\" href=\"javascript:closeEquationDialog();\"><span></span></a><span id="file-dialog-title-label">Masukkan Persamaan</span></div>\r\n'+
	'    <div id="equation-area" style="position:relative; height:480px; overflow:hidden;">\r\n'+
	'    </div>\r\n'+
	'</div>\r\n';
	let el = document.getElementById('dialog-area');
	el.innerHTML = html;
	el.setAttribute('data-dialog-shown', 'true');
	$('#equation-area').html('<iframe src="equation.html" frameborder="0" style="padding:0;margin:0;width:100%;height:500px;"></iframe>');
}
function showChemistryDialog()
{
	let html = ''+
	'<div class="file-dialog" style="width:800px">\r\n'+
	'	<div class="file-dialog-title"><a class=\"file-dialog-close-icon\" href=\"javascript:closeChemistryDialog();\"><span></span></a><span id="file-dialog-title-label">Editor Molekul</span></div>\r\n'+
	'    <div id="kekule-area" style="position:relative; height:480px; overflow:hidden;">\r\n'+
	'    </div>\r\n'+
	'</div>\r\n';
	let el = document.getElementById('dialog-area');
	el.innerHTML = html;
	el.setAttribute('data-dialog-shown', 'true');
	$('#kekule-area').html('<iframe src="chem-editor.html" frameborder="0" style="padding:0;margin:0;width:100%;height:500px;"></iframe>');
}
function showLatexDialog()
{
	let inputElement = $('#input')[0];
	let selectedText=getInputSelection(inputElement);
	let textValue = inputElement.value;
	let start = selectedText.start;
	let end = selectedText.end;
	let textSelected = textValue.substr(start, end-start);
	textSelected = textSelected.trim();

	let html = ''+
	'<div class="file-dialog" style="width:640px">\r\n'+
	'	<div class="file-dialog-title"><a class=\"file-dialog-close-icon\" href=\"javascript:closeEquationDialog();\"><span></span></a><span id="file-dialog-title-label">Masukkan Persamaan</span></div>\r\n'+
	'    <div id="equation-area" style="position:relative; height:352px; overflow:hidden;">\r\n'+
	'    </div>\r\n'+
	'</div>\r\n';
	let el = document.getElementById('dialog-area');
	el.innerHTML = html;
	el.setAttribute('data-dialog-shown', 'true');
	let url = 'latex.html#arg='+encodeURIComponent(textSelected);
	$('#equation-area').html('<iframe src="'+url+'" frameborder="0" style="padding:0;margin:0;width:100%;height:400px;"></iframe>');
}
function showSuperscriptDialog()
{
	let html = ''+
	'<div class="file-dialog" style="width:680px">\r\n'+
	'	<div class="file-dialog-title"><a class=\"file-dialog-close-icon\" href=\"javascript:closeEquationDialog();\"><span></span></a><span id="file-dialog-title-label">Masukkan Superscript</span></div>\r\n'+
	'    <div id="equation-area" style="position:relative; height:372px; overflow:hidden;">\r\n'+
	'    </div>\r\n'+
	'</div>\r\n';
	let el = document.getElementById('dialog-area');
	el.innerHTML = html;
	el.setAttribute('data-dialog-shown', 'true');
	$('#equation-area').html('<iframe src="superscript.html" frameborder="0" style="padding:0;margin:0;width:100%;height:400px;"></iframe>');
}
function showSubscriptDialog()
{
	let html = ''+
	'<div class="file-dialog" style="width:680px">\r\n'+
	'	<div class="file-dialog-title"><a class=\"file-dialog-close-icon\" href=\"javascript:closeEquationDialog();\"><span></span></a><span id="file-dialog-title-label">Masukkan Subscript</span></div>\r\n'+
	'    <div id="equation-area" style="position:relative; height:372px; overflow:hidden;">\r\n'+
	'    </div>\r\n'+
	'</div>\r\n';
	let el = document.getElementById('dialog-area');
	el.innerHTML = html;
	el.setAttribute('data-dialog-shown', 'true');
	$('#equation-area').html('<iframe src="subscript.html" frameborder="0" style="padding:0;margin:0;width:100%;height:400px;"></iframe>');
}
function showSubSuperscriptDialog()
{
	let html = ''+
	'<div class="file-dialog" style="width:600px">\r\n'+
	'	<div class="file-dialog-title"><a class=\"file-dialog-close-icon\" href=\"javascript:closeEquationDialog();\"><span></span></a><span id="file-dialog-title-label">Masukkan Subscript &amp; Superscript</span></div>\r\n'+
	'    <div id="equation-area" style="position:relative; height:278px; overflow:hidden;">\r\n'+
	'    </div>\r\n'+
	'</div>\r\n';
	let el = document.getElementById('dialog-area');
	el.innerHTML = html;
	el.setAttribute('data-dialog-shown', 'true');
	$('#equation-area').html('<iframe src="subsuperscript.html" frameborder="0" style="padding:0;margin:0;width:100%;height:306px;"></iframe>');
}
function showSymbolDialog()
{
	let html = ''+
	'<div class="file-dialog" style="width:680px">\r\n'+
	'	<div class="file-dialog-title"><a class=\"file-dialog-close-icon\" href=\"javascript:closeEquationDialog();\"><span></span></a><span id="file-dialog-title-label">Masukkan Simbol</span></div>\r\n'+
	'    <div id="equation-area" style="position:relative; height:372px; overflow:hidden;">\r\n'+
	'    </div>\r\n'+
	'</div>\r\n';
	let el = document.getElementById('dialog-area');
	el.innerHTML = html;
	el.setAttribute('data-dialog-shown', 'true');
	$('#equation-area').html('<iframe src="symbol.html" frameborder="0" style="padding:0;margin:0;width:100%;height:400px;"></iframe>');
}

function insertEquation(latex)
{
	insertAtCursor(document.getElementById('input'), latex);
	let elemInput = document.getElementById('input');
	let elemOutput = document.getElementById('preview2');
	let elemStatus = document.getElementById('status');
	renderQuestion(elemInput, elemOutput, elemStatus);
	closeEquationDialog();
}
function closeEquationDialog()
{
	let el = document.getElementById('dialog-area');
	el.setAttribute('data-dialog-shown', 'false');
	setTimeout(function(){
		el.innerHTML = '';
	}, 200);
}
function closeChemistryDialog()
{
	closeEquationDialog();
}

function deleteFileFromStorage()
{
	let filename = document.getElementById('filename').value;
	pbDialog({
	modal:true,
	width:360,
	height:190,
	title:'Konfirmasi',
	content:'<p>Apakah Anda akan menghapus ujian ini?</p>',
	buttons:{
		'Ya':function(){
			filename = filename.trim();
			filename = filename.replace(/[^0-9A-Za-z_\-\(\)]/gi, ' '); //NOSONAR
			filename = filename.replace(/ {1,}/g," ").trim(); //NOSONAR
		
			let i;
			let fls = window.localStorage.getItem('filelist') || '[]';
			if(fls == '[null]') fls = '[]';
			fileNameList =  eval(fls);
			let fls2 = [];
			for(i in fileNameList)
			{
				if(fileNameList[i] != filename)
				{
					fls2.push(fileNameList[i]);
				}
			}
			window.localStorage.removeItem('data_'+filename);
			window.localStorage.setItem('filelist', JSON.stringify(fls2));
		
			let html = '<ul>';
			for(i in fls2)
			{
				html += '<li><a href="javascript:selectFileList(\''+fls2[i]+'\')">'+fls2[i]+'</a></li>\r\n';
			}
			html += '</ul>';
			document.getElementById('file-dialog-list').innerHTML = html;
			document.getElementById('filename').value = '';
			closeDialog();
		},
		'Tidak':function(){
			closeDialog();
		}
	}
	});	
}
function deleteFile()
{
	showDialogDeleteFile();
	let fls = window.localStorage.getItem('filelist') || '[]';
	fileNameList =  eval(fls);
	let i;
	let html = '<ul>';
	for(i in fileNameList)
	{
		html += '<li class="file-item"><a href="javascript:selectFileList(\''+fileNameList[i]+'\')">'+fileNameList[i]+'</a></li>\r\n';
	}
	html += '</ul>';
	document.getElementById('file-dialog-list').innerHTML = html;
}
function openFile()
{
	showDialogOpenFile();
	let fls = window.localStorage.getItem('filelist') || '[]';
	fileNameList =  eval(fls);
	let i;
	let html = '<ul>';
	for(i in fileNameList)
	{
		html += '<li class="file-item"><a href="javascript:selectFileList(\''+fileNameList[i]+'\')">'+fileNameList[i]+'</a></li>\r\n';
	}
	html += '</ul>';
	document.getElementById('file-dialog-list').innerHTML = html;
}
function saveFileAs()
{
	showDialogSaveFile(currentFileName);
	let fls = window.localStorage.getItem('filelist') || '[]';
	fileNameList =  eval(fls);
	let i;
	let html = '<ul>';
	for(i in fileNameList)
	{
		html += '<li class="file-item"><a href="javascript:selectFileList(\''+fileNameList[i]+'\')">'+fileNameList[i]+'</li>';
	}
	html += '</ul>';
	document.getElementById('file-dialog-list').innerHTML = html;
}
function loadFile()
{
	let filename = document.getElementById('filename').value;
	filename = filename.trim();
	filename = filename.replace(/[^0-9A-Za-z_\-\(\)]/gi, ' '); //NOSONAR
	filename = filename.replace(/ {1,}/g," ").trim(); //NOSONAR
	if(filename == '')
	{
		alert('Silakan masukkan nama file');
		document.getElementById('filename').select();
	}
	else
	{
		currentFileName = filename;
		loadFileData(currentFileName);
		closeDialogFile();
	}
}
function closeDialogFile()
{
	closeEquationDialog();
}
function enterSign()
{
	insertAtCursor(document.getElementById('input'), "\\\\\n");
}
function insertImageString()
{
	let filename = document.getElementById('filename').value;
	if(filename == '')
	{
		alert('Silakan masukkan nama file');
		document.getElementById('filename').select();
	}
	else
	{
		insertAtCursor(document.getElementById('input'), "img:"+filename+" ");
		closeDialogFile();
	}
}
function insertAudioString()
{
	let filename = document.getElementById('filename').value;
	if(filename == '')
	{
		alert('Silakan masukkan nama file');
		document.getElementById('filename').select();
	}
	else
	{
		insertAtCursor(document.getElementById('input'), "audio:"+filename+"#text-top#300,40 ");
		closeDialogFile();
	}
}
function uploadImage()
{
	document.getElementById('image').click();
}
function uploadAudio()
{
	document.getElementById('audio').click();
}
function uploadAudioCompress()
{
	document.getElementById('audio2').click();
}
function insertImage()
{
	let html = ''+
	'<div class="file-dialog">\r\n'+
	'	<div class="file-dialog-title"><a class=\"file-dialog-close-icon\" href=\"javascript:closeDialogFile();\"><span></span></a><span id="file-dialog-title-label">Masukkan Gambar</span></div>\r\n'+
	'    <div id="file-dialog-list"><div class="progressbar"><div class="progressbar-inner"></div></div>\r\n'+
	'    <div id="remote-image-list">\r\n'+
	'    </div>\r\n'+
	'    </div>\r\n'+
	'    <div class="file-dialog-control">\r\n'+
	'    <div class="file-dialog-form">\r\n'+
	'    	 <form name="filefrm" id="filefrm" onsubmit="insertImageString(); return false;">\r\n'+
	'        	 <input type="text" name="filename" id="filename" autocomplete="off" style="width:calc(100% - 264px);">\r\n'+
	'            <input type="button" name="openbtn" id="openbtn" value="Masukkan" onclick="insertImageString()">\r\n'+
	'            <input type="button" name="uploadbtn" id="openbtn" value="Unggah" onclick="uploadImage()">\r\n'+
	'            <input type="button" name="closebtn" id="closebtn" value="Batal" onclick="closeDialogFile()">\r\n'+
	'        </form>\r\n'+
	'    </div>\r\n'+
	'    </div>\r\n'+
	'</div>\r\n';
	let el = document.getElementById('dialog-area');
	el.innerHTML = html;
	el.setAttribute('data-dialog-shown', 'true');
	$.get('ajax-load-image.php', {test_id:testID}, function(answer){
		$('#remote-image-list').html(answer);
	});
	initDragDropUpload();	
}
function insertAudio()
{
	let html = ''+
	'<div class="file-dialog">\r\n'+
	'	<div class="file-dialog-title"><a class=\"file-dialog-close-icon\" href=\"javascript:closeDialogFile();\"><span></span></a><span id="file-dialog-title-label">Masukkan Suara</span></div>\r\n'+
	'    <div id="file-dialog-list"><div class="progressbar"><div class="progressbar-inner"></div></div>\r\n'+
	'    <div id="remote-image-list">\r\n'+
	'    </div>\r\n'+
	'    </div>\r\n'+
	'    <div class="file-dialog-control">\r\n'+
	'    <div class="file-dialog-form">\r\n'+
	'    	 <form name="filefrm" id="filefrm" onsubmit="insertAudioString(); return false;">\r\n'+
	'        	 <input type="text" name="filename" id="filename" autocomplete="off" style="width:calc(100% - 264px);">\r\n'+
	'            <input type="button" name="openbtn" id="openbtn" value="Masukkan" onclick="insertAudioString()">\r\n'+
	'            <input type="button" name="uploadbtn" id="openbtn" value="Unggah" onclick="uploadAudio()">\r\n'+
	'            <input type="button" name="closebtn" id="closebtn" value="Batal" onclick="closeDialogFile()">\r\n'+
	'        </form>\r\n'+
	'    </div>\r\n'+
	'    </div>\r\n'+
	'</div>\r\n';
	let el = document.getElementById('dialog-area');
	el.innerHTML = html;
	el.setAttribute('data-dialog-shown', 'true');
	$.get('ajax-load-audio.php', {test_id:testID}, function(answer){
		$('#remote-image-list').html(answer);
	});
	initDragDropUpload();	
}
function compressAudio()
{
	let html = ''+
	'<div class="file-dialog">\r\n'+
	'	<div class="file-dialog-title"><a class=\"file-dialog-close-icon\" href=\"javascript:closeDialogFile();\"><span></span></a><span id="file-dialog-title-label">Kompres Suara</span></div>\r\n'+
	'    <div id="file-dialog-list"><div class="progressbar"><div class="progressbar-inner"></div></div>\r\n'+
	'    <div id="remote-image-list">\r\n'+
	'    </div>\r\n'+
	'    </div>\r\n'+
	'    <div class="file-dialog-control">\r\n'+
	'    <div class="file-dialog-form">\r\n'+
	'    	 <form name="filefrm" id="filefrm" onsubmit="insertAudioString(); return false;">\r\n'+
	'        	 <input type="text" name="filename" id="filename" autocomplete="off" style="width:calc(100% - 264px);">\r\n'+
	'            <input type="button" name="openbtn" id="openbtn" value="Masukkan" onclick="insertAudioString()">\r\n'+
	'            <input type="button" name="uploadbtn" id="openbtn" value="Unggah" onclick="uploadAudioCompress()">\r\n'+
	'            <input type="button" name="closebtn" id="closebtn" value="Batal" onclick="closeDialogFile()">\r\n'+
	'        </form>\r\n'+
	'    </div>\r\n'+
	'    </div>\r\n'+
	'</div>\r\n';
	let el = document.getElementById('dialog-area');
	el.innerHTML = html;
	el.setAttribute('data-dialog-shown', 'true');
	$.get('ajax-load-compress-audio.php', {test_id:testID}, function(answer){
		$('#remote-image-list').html(answer);
	});
	initDragDropUpload();	
}
function showDialogOpenFile()
{
	let html = ''+
	'<div class="file-dialog">\r\n'+
	'	<div class="file-dialog-title"><a class=\"file-dialog-close-icon\" href=\"javascript:closeDialogFile();\"><span></span></a><span id="file-dialog-title-label">Buka Soal Ujian</span></div>\r\n'+
	'    <div id="file-dialog-list">\r\n'+
	'    </div>\r\n'+
	'    <div class="file-dialog-control">\r\n'+
	'    <div class="file-dialog-form">\r\n'+
	'    	 <form name="filefrm" id="filefrm" onsubmit="loadFile(); return false;">\r\n'+
	'        	 <input type="text" name="filename" id="filename">\r\n'+
	'            <input type="button" name="openbtn" id="openbtn" value="Buka" onclick="loadFile()">\r\n'+
	'            <input type="button" name="closebtn" id="closebtn" value="Batal" onclick="closeDialogFile()">\r\n'+
	'        </form>\r\n'+
	'    </div>\r\n'+
	'    </div>\r\n'+
	'</div>\r\n';
	let el = document.getElementById('dialog-area');
	el.innerHTML = html;
	el.setAttribute('data-dialog-shown', 'true');
}
function showDialogDeleteFile()
{
	let html = ''+
	'<div class="file-dialog">\r\n'+
	'	<div class="file-dialog-title"><a class=\"file-dialog-close-icon\" href=\"javascript:closeDialogFile();\"><span></span></a><span id="file-dialog-title-label">Hapus Soal Ujian</span></div>\r\n'+
	'    <div id="file-dialog-list">\r\n'+
	'    </div>\r\n'+
	'    <div class="file-dialog-control">\r\n'+
	'    <div class="file-dialog-form">\r\n'+
	'    	 <form name="filefrm" id="filefrm" onsubmit="deleteFileFromStorage(); return false;">\r\n'+
	'        	 <input type="text" name="filename" id="filename">\r\n'+
	'            <input type="button" name="deletebtn" id="deletebtn" value="Hapus" onclick="deleteFileFromStorage()">\r\n'+
	'            <input type="button" name="closebtn" id="closebtn" value="Tutup" onclick="closeDialogFile()">\r\n'+
	'        </form>\r\n'+
	'    </div>\r\n'+
	'    </div>\r\n'+
	'</div>\r\n';
	let el = document.getElementById('dialog-area');
	el.innerHTML = html;
	el.setAttribute('data-dialog-shown', 'true');
}
function showDialogSaveFile(filename)
{
	filename = filename || '';
	let html = ''+
	'<div class="file-dialog">\r\n'+
	'	<div class="file-dialog-title"><a class=\"file-dialog-close-icon\" href=\"javascript:closeDialogFile();\"><span></span></a><span id="file-dialog-title-label">Simpan Soal Ujian</span></div>\r\n'+
	'    <div id="file-dialog-list">\r\n'+
	'    </div>\r\n'+
	'    <div class="file-dialog-control">\r\n'+
	'    <div class="file-dialog-form">\r\n'+
	'    	 <form name="filefrm" id="filefrm" onsubmit="writeFile(); return false;">\r\n'+
	'        	 <input type="text" name="filename" id="filename">\r\n'+
	'            <input type="button" name="savebtn" id="savebtn" value="Simpan" onclick="writeFile()">\r\n'+
	'            <input type="button" name="closebtn" id="closebtn" value="Batal" onclick="closeDialogFile()">\r\n'+
	'        </form>\r\n'+
	'    </div>\r\n'+
	'    </div>\r\n'+
	'</div>\r\n';
	let el = document.getElementById('dialog-area');
	el.innerHTML = html;
	el.setAttribute('data-dialog-shown', 'true');
	document.getElementById('filename').value = filename;
}
function showTestInfo(test_id)
{
	let html = ''+
	'<div class="file-dialog">\r\n'+
	'	<div class="file-dialog-title"><a class=\"file-dialog-close-icon\" href=\"javascript:closeDialogFile();\"><span></span></a><span id="file-dialog-title-label">Informasi Ujian</span></div>\r\n'+
	'    <div id="file-dialog-list" style="height:300px;">\r\n'+
	'    </div>\r\n'+
	'</div>\r\n';
	$.get('ajax-load-test-info.php', {test_id:test_id}, function(answer){
		$('#file-dialog-list').append(answer);
	});
	let el = document.getElementById('dialog-area');
	el.innerHTML = html;
	el.setAttribute('data-dialog-shown', 'true');
}
function newFile()
{
	let currentData = document.getElementById('input').value;
	if(contentModified)
	{
		if(currentFileName != '')
		{

			pbDialog({
				modal:true,
				width:360,
				height:190,
				title:'Konfirmasi',
				content:'<p>Apakah Anda akan menyimpan '+currentFileName+'?</p>',
				buttons:{
					'Ya':function(){
						saveFileData(currentFileName, currentData);
						animateSaving();
						contentModified = false;
						currentFileName = '';
						document.getElementById('input').value = '';
						document.getElementById('preview2').innerHTML = '';
						closeDialog();
					},
					'Tidak':function(){
						contentModified = false;
						currentFileName = '';
						document.getElementById('input').value = '';
						document.getElementById('preview2').innerHTML = '';
						closeDialog();
					},
					'Batal':function(){
						closeDialog();
					}
				}
			});
		}
		else
		{
			pbDialog({
				modal:true,
				width:360,
				height:190,
				title:'Konfirmasi',
				content:'<p>Apakah Anda akan menyimpan soal-soal ini?</p>',
				buttons:{
					'Ya':function(){
						closeDialog();
						saveFileAs();
					},
					'Tidak':function(){
						closeDialog();
						contentModified = false;
						currentFileName = '';
						document.getElementById('input').value = '';
						document.getElementById('preview2').innerHTML = '';
					},
					'Batal':function(){
						closeDialog();
					}
				}
			});
		}
	}
	else
	{
		currentFileName = '';
		document.getElementById('input').value = '';
		document.getElementById('preview2').innerHTML = '';
	}
}
function restartQuestion()
{
	let startFrom = 1;
	modContinue = false;
	document.getElementById('toolbar-continue').classList.remove('toolbar-selected');
	document.getElementById('toolbar-restart').classList.add('toolbar-selected');
	document.getElementById('preview1').style.display = 'none';
	$('#preview2 > ol > li:first-child').attr('value', startFrom);

	let elemInput = document.getElementById('input');
	let elemOutput = document.getElementById('preview2');
	let elemStatus = document.getElementById('status');
	renderQuestion(elemInput, elemOutput, elemStatus);

}
let modContinue = false;
function continueQuestion()
{
	let startFrom = 1;
	modContinue = true;
	document.getElementById('toolbar-restart').classList.remove('toolbar-selected');
	document.getElementById('toolbar-continue').classList.add('toolbar-selected');
	$.get('ajax-load-stored-question.php', {test_id:testID, edit_mode:(editMode)?1:0}, function(answer){
		$('#preview1').html(answer);
		$('#preview1').css({'display':'block'});
		$('#question-separator').css({'display':'block'});
		let num = $('#preview1 > ol > li').length;
		startFrom = num+1;
		$('#preview2 > ol > li:first-child').attr('value', startFrom);

		let elemInput = document.getElementById('input');
		let elemOutput = document.getElementById('preview2');
		let elemStatus = document.getElementById('status');
		renderQuestion(elemInput, elemOutput, elemStatus);
	});
	document.getElementById('preview1').style.display = 'block';
}
function appendToTest()
{
	// check te answer
	let input = document.getElementById('input').value;
	let questions = buildQuestion(input);
	let na = noAnswer(questions);
	if(na.length > 0)
	{
		pbDialog({
			modal:true,
			width:360,
			height:140,
			title:'Soal Tidak Lengkap',
			content:'<p>Soal yang Anda buat tidak lengkap? Anda '+na.length+' soal yang tidak memiliki jawaban.</p>\r\n'+
				'<p>Soal tanpa jawaban yaitu '+na.join(', ')+'.</p>\r\n<p>Silakan perbaiki dahulu soal Anda.</p>\r\n',
			buttons:{
				'Tutup':function(){
					closeDialog();
				}
			}
		});
	}
	else
	{
		let startFrom = 1;
		pbDialog({
			modal:true,
			width:360,
			height:140,
			title:'Memasukkan Soal',
			content:'<p>Apakah Anda akan memasukkan soal-soal ini ke dalam ujian?</p>',
			buttons:{
				'Ya':function(){
					let question_text = document.getElementById('input').value;
					$.post('ajax-append-question.php', {question_text:question_text,test_id:testID, edit_mode:(editMode)?1:0, option:'add'}, function(answer){
						$('#preview1').html(answer);
						$('#preview1').css({'display':'block'});
						$('#question-separator').css({'display':'block'});
						let num = $('#preview1 > ol > li').length;
						startFrom = num+1;
						$('#preview2 > ol > li:first-child').attr('value', startFrom);
						document.getElementById('toolbar-restart').classList.remove('toolbar-selected');
						document.getElementById('toolbar-continue').classList.add('toolbar-selected');
						document.getElementById('preview1').style.display = 'block';
						document.getElementById('input').value = '';
						document.getElementById('input').focus();
					});
					closeDialog();
				},
				'Tidak':function(){
					closeDialog();
				}
			}
		});
	}
}
function downloadFile()
{
	let value = document.getElementById('input').value;
	value = value.replaceAll("\n", "\r\n");
	let blob = new Blob([value], {type: 'text/plain'});
	saveAs(blob, (currentFileName!='')?(currentFileName+'.txt'):'Soal-Ujian.txt', 'text/plain');
}
function downloadFileHTML()
{
	let value = '<!DOCTYPE html>\r\n'+
	'<html lang="en">\r\n'+
	'<head>\r\n'+
	'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />\r\n'+
	'<style type="text/css">span.score{display:none;}</style>\r\n'+
	'<title>Soal Ujian</title>\r\n'+
	'</head>\r\n'+
	'<body>\r\n'+
	document.getElementById('preview2').innerHTML+
	'\r\n'+
	'</body>\r\n'+
	'</html>';
	value = value.split('<span class="score"></span>').join('');

	let blob = new Blob([value], {type: 'text/html'});
	saveAs(blob, (currentFileName!='')?(currentFileName+'.html'):'Soal-Ujian.html', 'text/html');
}
function downloadFileWord()
{
	let value = '<!DOCTYPE html>\r\n'+
	'<html lang="en">\r\n'+
	'<head>\r\n'+
	'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />\r\n'+
	'<style type="text/css">span.score{display:none;} .container{} .container p, .container li{line-height:1.6;}</style>\r\n'+
	'<title>Soal Ujian</title>\r\n'+
	'</head>\r\n'+
	'<body>\r\n'+
	'<div class="container">\r\n'+
	document.getElementById('preview2').innerHTML+
	'\r\n'+
	'</div>\r\n'+
	'</body>\r\n'+
	'</html>';
	value = value.split('<span class="score"></span>').join('');
	let blob = new Blob([value], {type: 'application/vnd.doc'});
	saveAs(blob, (currentFileName!='')?(currentFileName+'.doc'):'Soal-Ujian.doc', 'application/vnd.doc');
}
function toXML(key, val)
{
	return '<'+key+'>'+val+'</'+key+'>\r\n';
}
function downloadFileXML()
{
	let questions = buildQuestion(document.getElementById('input').value);
	let i;
	let j;
	let question;
	let options;
	let option;
	let xml;
	xml = '<?xml version="1.0" encoding="utf-8"?>\r\n<test>\r\n';
	for(i in questions)
	{
		question = questions[i];
		options = question.options;
		xml += '<item>\r\n';
		xml += '<question><text>'+(question.question.escapeHTMLEntities().escapeHTML())+'</text><random>1</random><numbering>'+question.numbering+'</numbering></question>\r\n';
		xml += '<answer>\r\n';
		for(j in options)
		{
			option = options[j];
			xml += '<option><text>'+(option.text.escapeHTMLEntities().escapeHTML())+'</text><value>'+option.score+'</value></option>';
		}
		xml += '</answer>\r\n';
		xml += '</item>\r\n';
	}
	xml += '</test>\r\n';
	let blob = new Blob([xml], {type: 'text/xml'});
	saveAs(blob, (currentFileName!='')?(currentFileName+'.xml'):'Soal-Ujian.xml', 'text/xml');
}
function uploadFile()
{
	document.getElementById('file').click();
}
let editMode = false;
function toggleEditMode()
{
	editMode = !editMode;
	if(editMode)
	{
		$('#toolbar-edit').addClass('toolbar-selected');
	}
	else
	{
		$('#toolbar-edit').removeClass('toolbar-selected');
	}
	continueQuestion();
}
function printDiv(divId, answer) {
	
	let frm = frames['print_frame'].document;
	let style = frm.createElement('style');
	
	style.appendChild(document.createTextNode(
		'li{\r\n'+
		'line-height:1.6;\r\n'+
		'} \r\n'+
		'li > span{\r\n'+
		'display:block;\r\n'+
		'}\r\n'+
		'ol li table{\r\n'+
		'border-collapse:collapse;\r\n'+
		'margin-top:2px;\r\n'+
		'margin-bottom:2px;\r\n'+
		'}\r\n'+
		'ol li table td{\r\n'+
		'padding:3px 5px;\r\n'+
		'}\r\n'+
		'ol li table td table{\r\n'+
		'margin-top:0;\r\n'+
		'margin-bottom:0;\r\n'+
		'}\r\n'+
		'ol li table[border="1"]{\r\n'+
		'border-collapse:collapse;\r\n'+
		'}\r\n'+
		'ol li table[border="1"] td{\r\n'+
		'padding:4px 5px;\r\n'+
		'}\r\n'+
		'ol li table[border="0"] td{\r\n'+
		'padding:4px 0px;\r\n'+
		'}\r\n'+
		'ol li table[border="1"] thead td{\r\n'+
		'font-weight:bold;\r\n'+
		'background-color:#CCCCCC;\r\n'+
		'}\r\n'+
		'ol li table[border="1"] tfoot td{\r\n'+
		'background-color:#F8F8F8;\r\n'+
		'}\r\n'+
		''
	));
	let html = document.getElementById(divId).innerHTML;
	if(answer)
	{
		let obj = $('<div id="preview">'+html+'<div>');
		obj.find('#preview1 ol li ol li').each(function(index, element) {
			let obj2 = $(this);
			if(obj2.find('.score').length)
			{
				obj2.css({'font-weight':'bold', 'color':'#06C'});
				obj2.find('span').css({'font-weight':'normal', 'color':'#000000'});
			}
		});
		obj.find('#preview2 ol li ol li').each(function(index, element) {
			let obj2 = $(this);
			if(obj2.find('.score').length)
			{
				obj2.css({'font-weight':'bold', 'color':'#06C'});
				obj2.find('span').css({'font-weight':'normal', 'color':'#000000'});
			}
		});
		html = obj[0].outerHTML;
	}
	window.frames["print_frame"].document.body.innerHTML = html;

	let otherhead = frm.getElementsByTagName("body")[0];
	otherhead.appendChild(style);
	window.frames["print_frame"].window.focus();
	window.frames["print_frame"].window.print();
}
function printFile(answer)
{
	answer = answer || false;
	let iframe = document.createElement('iframe');
	iframe.setAttribute('id', 'print_frame');
	iframe.setAttribute('name', 'print_frame');
	iframe.setAttribute('width', '0');
	iframe.setAttribute('height', '0');
	iframe.setAttribute('frameborder', '0');
	iframe.setAttribute('src', 'about:blank');
	iframe.style.position = 'absolute';
	iframe.style.top = '-10000px';
	iframe.style.left = '-10000px';
	let pageBody = document.getElementsByTagName('body')[0];
	pageBody.appendChild(iframe);
	printDiv('preview', answer);
	iframe.remove();
}
function animateSaving()
{
	let el = document.createElement('div');
	el.setAttribute('id', 'animation-saving');
	document.getElementsByTagName('body')[0].appendChild(el);
	el.setAttribute('data-anim', 'false');
	el.setAttribute('data-anim', 'true');
	setTimeout(function(){
		el.remove();
	}, 400);
}
function copyExternalImage(url)
{
	$.ajax({
		url:'tools/ajax/upload-file-multi.php?option=copyexternal&ujian_id='+testID, 
		data:{url:url},
		type:'POST',
		dataType:'html',
		success: function(data)
		{
			insertAtCursor(document.getElementById('input'), 'img:'+data+' ');
		}
	});
}
function uploadBase64Image(data, valign, ext)
{
	valign = valign || '';
	$('.progressbar-fixed > div').css({'width':'0%'});
	$('.progressbar-fixed').css({'display':'block'});
	$.ajax({
		url:'ajax-upload-file-multi.php?option=uploadbase64image&test_id='+testID, 
		data:{data:data,ext:ext},
		type:'POST',
		dataType:'html',
		success: function(responseText)
		{
			insertAtCursor(document.getElementById('input'), 'img:'+responseText+valign+' ');
			$('.progressbar-fixed').css({'display':'none'});
		},
		xhr: function() {  // custom xhr
			let myXhr = $.ajaxSettings.xhr();
			if(myXhr.upload){ // check if upload property exists
				myXhr.upload.addEventListener('progress', updateProgressFixed, false); // for handling the progress of the upload
			}
			return myXhr;
		}

	});
}
function uploadBase64ImageFromLatex(data, editor, ext, latex)
{
	ext = ext || 'png';
	$('.progressbar-fixed > div').css({'width':'0%'});
	$('.progressbar-fixed').css({'display':'block'});
	$.ajax({
		url:'ajax-upload-file-multi.php?option=uploadbase64image&test_id='+testID, 
		data:{data:data,ext:ext},
		type:'POST',
		dataType:'html',
		success: function(responseText)
		{
			let desc = ' ';
			if(latex)
			{
				desc = '##'+encodeURIComponent(latex);
			}
			insertAtCursor(document.getElementById('input'), 'img:'+responseText+'#middle'+desc+' ');
			let elemInput = document.getElementById('input');
			let elemOutput = document.getElementById('preview2');
			let elemStatus = document.getElementById('status');
			renderQuestion(elemInput, elemOutput, elemStatus);
			closeEquationDialog();
			$('.progressbar-fixed').css({'display':'none'});
		},
		xhr: function() {  // custom xhr
			let myXhr = $.ajaxSettings.xhr();
			if(myXhr.upload){ // check if upload property exists
				myXhr.upload.addEventListener('progress', updateProgressFixed, false); // for handling the progress of the upload
			}
			return myXhr;
		}
	});
}

function handlePasteImage(e) //NOSONAR
{
	if (e && e.clipboardData && e.clipboardData.getData) 
	{
		if((/Files/.test(e.clipboardData.types) && !/text\/html/.test(e.clipboardData.types))) {
			// Paste image from other application
			let blob = e.clipboardData.items[0].getAsFile();
			let reader = new window.FileReader();
			reader.readAsDataURL(blob); 
			reader.onloadend = function(){
				// reader.result is base64 encoded image
				uploadBase64Image(reader.result, '#text-top');
			}
			if(e.preventDefault)
			{
				e.stopPropagation();
				e.preventDefault();
			}
		}
		else if(/Files/.test(e.clipboardData.types) && /text\/html/.test(e.clipboardData.types))
		{
			// Paste image from web application
			let html = e.clipboardData.getData('text/html');
			let container = document.createElement('div');
			container.innerHTML = html;
			let nimage = 0;
			for(let i in container.childNodes)
			{
				if(container.childNodes.item(i).tagName == 'IMG' || container.childNodes.item(i).tagName == 'img')
				{
					let url = container.childNodes.item(i).getAttribute('src');
					copyExternalImage(url);
				}
				nimage++;
			}
			if(nimage > 0)
			{
				if(e.preventDefault)
				{
					e.stopPropagation();
					e.preventDefault();
				}
			}
		}
		else if(/text\/html/.test(e.clipboardData.types)) 
		{
			// convert HTML to plain text
			let data = e.clipboardData.getData('text/html');
			try{
				e.clipboardData.setData('text/plain', data);
			}
			catch(e)
			{
				// Do nothing
			}
			
			if(e.preventDefault)
			{
				// Do nothing
			}
		}
		else if(/text\/plain/.test(e.clipboardData.types)) 
		{
			// Do nothing
		}
	}
}
window.onload = function(){
	document.getElementById('input').addEventListener('change', function(e){
		inputChanged(e);
		contentModified = true;
	});
	document.getElementById('input').addEventListener('keyup', function(e){
		let event;
		if(typeof document.createEvent != 'undefined')
		{
			event = document.createEvent('KeyboardEvent');
			event.initEvent("change", true, true);		
		}
		else
		{
			event = new Event('change');
		}
		e.target.dispatchEvent(event);
	});
	document.getElementById('input').addEventListener('focus', function(e){
		let event;
		if(typeof document.createEvent != 'undefined')
		{
			event = document.createEvent('KeyboardEvent');
			event.initEvent("change", true, true);		
		}
		else
		{
			event = new Event('change');
		}
		e.target.dispatchEvent(event);
	});
	document.getElementById('input').addEventListener('keydown', function(e){
		// Ctrl + S
		if (e.keyCode == 83 && (navigator.platform.match("Mac") ? e.metaKey : e.ctrlKey))
		{
			e.preventDefault();
			if(currentFileName != '')
			{
				saveFileData(currentFileName, e.target.value);
				animateSaving();
			}
			else
			{
				saveFileAs();
			}
		}
		// Ctrl + E	
		if ((e.keyCode == 69 || e.keyCode == 81)&& (navigator.platform.match("Mac") ? e.metaKey : e.ctrlKey))
		{
			showLatexDialog();
			e.preventDefault();
			e.stopPropagation();
		}
		// Ctrl + W
		// Prevent close window	
		if (e.keyCode == 87 && (navigator.platform.match("Mac") ? e.metaKey : e.ctrlKey))
		{
			e.preventDefault();
			e.stopPropagation();
		}	
	});
	document.getElementById('input').addEventListener('keypress', function(e){
		if(
		((e.keyCode == 13 || e.keyCode == 10) && (navigator.platform.match("Mac") ? e.metaKey : e.ctrlKey))
		||
		((e.keyCode == 13 || e.keyCode == 10) && (navigator.platform.match("Mac") ? e.metaKey : e.shiftKey))
		)
		{
			insertAtCursor(e.target, "\\\\\n");
			e.preventDefault();
			e.stopPropagation();
		}	
	});
	document.getElementById('input').addEventListener('paste', function(e){
		handlePasteImage(e);
		let event;
		setTimeout(function(){
			if(typeof document.createEvent != 'undefined')
			{
				event = document.createEvent('KeyboardEvent');
				event.initEvent("change", true, true);		
			}
			else
			{
				event = new Event('change');
			}
			e.target.dispatchEvent(event);
		}, 500);
	});
	document.getElementById('input').focus();
	document.getElementById('file').onchange = function(){	
		let file = this.files[0];	
		let reader = new FileReader();
		reader.onload = function(progressEvent)
		{
			let lines = this.result;
			document.getElementById('input').value = lines;
			document.getElementById('input').focus();
		};
		reader.readAsText(file);
	};	
	document.getElementById('audio').onchange = function(){	
		let test_id = testID;
		let file = this.files[0];
		let frmdata = new FormData();
		frmdata.append('audios[]', file);
		$('.progressbar > div').css({'width':'0%'});
		$('.progressbar').css({'display':'block'});
		$.ajax({
			url:'ajax-upload-audio-multi.php?test_id='+test_id,
			type:"POST",
			processData:false,
			data:frmdata,
			dataType:"text",
			contentType:false,
			success:function(responseText){
				$('#remote-image-list').empty().append(responseText);
				$('.progressbar').css({'display':'none'});
			},
			xhr: function() {  // custom xhr
                let myXhr = $.ajaxSettings.xhr();
                if(myXhr.upload){ // check if upload property exists
                    myXhr.upload.addEventListener('progress', updateProgress, false); // for handling the progress of the upload
                }
                return myXhr;
            }
		});
			
	};	
	document.getElementById('audio2').onchange = function(){	
		let test_id = testID;
		let file = this.files[0];
		let frmdata = new FormData();
		frmdata.append('audios[]', file);
		$('.progressbar > div').css({'width':'0%'});
		$('.progressbar').css({'display':'block'});
		$.ajax({
			url:'ajax-upload-audio-multi.php?option=compress&test_id='+test_id,
			type:"POST",
			processData:false,
			data:frmdata,
			dataType:"text",
			contentType:false,
			success:function(responseText){
				$('#remote-image-list').empty().append(responseText);
				$('.progressbar').css({'display':'none'});
			},
			xhr: function() {  // custom xhr
                let myXhr = $.ajaxSettings.xhr();
                if(myXhr.upload){ // check if upload property exists
                    myXhr.upload.addEventListener('progress', updateProgress, false); // for handling the progress of the upload
                }
                return myXhr;
            }
		});
			
	};	
	$(document).on('change', '#image', function(e){	
		FileSelectHandler(e);
	});
	$(document).on('click', '#remote-image-list .img-li a, #remote-image-list .select-audio a', function(e){
		let name = $(this).attr('data-name');
		$('#filename').val(name);
		e.preventDefault();
	});
	$(document).on('change', '#file', function(e){
	});
	$(document).on('click', '.compress-audio-file', function(e){
		let filename = $(this).attr('data-name');
		let tr = $(this).closest('tr');
		let no = tr.find('td:first-child').text().toString().trim();
		let test_id = testID;
		let object = $(this);
		
		
		pbDialog({
				modal:true,
				width:360,
				height:190,
				title:'Konfirmasi',
				content:'<p>Mengompres file akan memperkecil ukuran file dan menurunkan kualitas suara. Pastikan bahwa Anda tidak mengompres file yang sama lebih dari satu kali.<br />Apakah Anda akan mengompres file '+filename+'?</p>',
				buttons:{
					'Ya':function(){
						object.replaceWith('<span class="animation-pressure"><span></span></span>')
						$.post('ajax-compress-audio-file.php', {filename:filename, no:no, test_id:test_id}, function(answer){
							tr.replaceWith(answer);
						});
						closeDialog();
					},
					'Tidak':function(){
						closeDialog();
					}
				}
			});
			
	});
	$(document).on('click', '#toolbar-info', function(e){
		let test_id = $(this).attr('data-test-id');
		showTestInfo(test_id);
	});
	contentModified = false;
}
function updateProgress(evt) {
    if (evt.lengthComputable) {
        let percentComplete = evt.loaded / evt.total;
		$('.progressbar-inner').css({'width':(percentComplete*100)+'%'});
    } 
}
function updateProgressFixed(evt) {
    if (evt.lengthComputable) {
        let percentComplete = evt.loaded / evt.total;
		$('.progressbar-fixed-inner').css({'width':(percentComplete*100)+'%'});
    } 
}
function pbDialog(options)
{
	options = options || {};
	let i;
	let settings = {
		content:'',
		width:300,
		height:120,
		modal:false,
		title:'Dialog',
		buttons:{
			'OK':function(){},
			'Cancel':function(){}
		}
	};
	for(let key in settings)
	{
		if(settings.hasOwnProperty(key) && typeof options[key] == 'undefined')
		{
			options[key] = settings[key];
		}
	}
	this.container = document.createElement('div');
	this.container.classList.add('pb-dialog-container');

	this.dialog = document.createElement('div');
	this.dialog.classList.add('pb-dialog');
	
	this.title = document.createElement('div');
	this.title.classList.add('pb-dialog-title');
	this.title.innerHTML = '<h3>'+options.title+'</h3>';
	
	this.content = document.createElement('div');
	this.content.classList.add('pb-dialog-content');
	this.content.innerHTML = options.content;

	this.buttons = document.createElement('div');
	this.buttons.classList.add('pb-dialog-buttons');
	
	
	for(i in options.buttons)
	{
		let btn = document.createElement('button');
		btn.innerHTML = i;
		btn.addEventListener('click', options.buttons[i]);
		this.buttons.appendChild(btn);
		this.buttons.appendChild(document.createTextNode(" "));
	}
	if(options.modal)
	{
		this.cover = document.createElement('div');
		this.cover.classList.add('pb-dialog-cover');
		this.container.appendChild(this.cover);
	}
	this.container.appendChild(this.dialog);
	this.dialog.appendChild(this.title);
	this.dialog.appendChild(this.content);
	this.dialog.appendChild(this.buttons);
	document.getElementsByTagName('body')[0].appendChild(this.container);
}
function closeDialog()
{
	document.getElementsByClassName('pb-dialog-container')[0].remove();
}

function insertAtCursor(myField, myValue) {
    //IE support
    if (document.selection) {
        myField.focus();
        let sel = document.selection.createRange();
        sel.text = myValue;
    }
    //MOZILLA and others
    else if (myField.selectionStart || myField.selectionStart == '0') {
        let startPos = myField.selectionStart;
        let endPos = myField.selectionEnd;
        myField.value = myField.value.substring(0, startPos)
            + myValue
            + myField.value.substring(endPos, myField.value.length);
			myField.setSelectionRange(startPos+myValue.length, startPos+myValue.length); 
			 myField.focus();
			
    } else {
        myField.value += myValue;
		myField.focus();
    }
}

function refreshList(testID)
{
	$.get('ajax-load-image.php',{test_id:testID}, function(answer){
		$('#remote-image-list').html(answer);
	});
}
let xhr;
if (window.XMLHttpRequest) 
{
	xhr = new XMLHttpRequest();
} 
else 
{
	let versions = [
		"MSXML2.XmlHttp.5.0", 
		"MSXML2.XmlHttp.4.0",
		"MSXML2.XmlHttp.3.0", 
		"MSXML2.XmlHttp.2.0",
		"Microsoft.XmlHttp"
	];
	for(let i = 0, len = versions.length; i < len; i++) 
	{
		try 
		{
			xhr = new ActiveObject(versions[i]);
			break;
		}
		catch(e){}
	} 
}	
function initDragDropUpload()
{
	if (window.File && window.FileList && window.FileReader)
	{
		let filedrag = $("#remote-image-list")[0];
		if (xhr.upload)
		{
			filedrag.addEventListener('dragenter', FileDragHover, false);
			filedrag.addEventListener('dragexit', FileDragHover, false);
			filedrag.addEventListener("dragover", FileDragHover, false);
			filedrag.addEventListener("dragleave", FileDragHover, false);
			filedrag.addEventListener("drop", FileSelectHandler, false);
			filedrag.style.display = "block";
		}
	}
}
function progressHandler(event){
	let percent = (event.loaded / event.total) * 100;
	$(".progressbar-inner").css({'width':percent+'%'})
}
function completeHandler(event){
	let response = event.target.responseText;
	$(".progressbar-inner").css({'width':'0%'});
	if(response!='')
	{
		$(".progressbar").css({'display':'none'});
	}
}
function errorHandler(event){
	alert('Terjadi kesalahan.');
}
function abortHandler(event){
	alert('Proses dibatalkan');
}
function FileDragHover(e)
{
	e.stopPropagation();
	e.preventDefault();
	if(e.type == "dragover") $('#remote-image-list').addClass('file-area-hover');
	else $('#remote-image-list').removeClass('file-area-hover');
}

function FileSelectHandler(e)
{
	FileDragHover(e);
	let test_id = testID;
	let textData;
	if(typeof e.dataTransfer != 'undefined')
	{
		textData = e.dataTransfer.getData('Text') || '';
	}
	else
	{
		textData = '';
	}
	if(textData.indexOf('://') > -1)
	{
		$.post('ajax-upload-file-multi.php?option=transfer&test_id='+test_id, {url:textData}, function(answer){
			refreshList(test_id);
		});
	}
	else
	{
	let files = e.target.files || e.dataTransfer.files;
	let formData = new FormData();
	for (let i = 0; i < files.length; i++)
	{
		if(i >= maxUploadFile-1)
		{
			break;
		}
		formData.append('images[]', files[i]);
	}
	$(".progressbar").css({'display':'block'});
	xhr.open('POST', 'ajax-upload-file-multi.php?test_id='+testID);
	xhr.onload = function ()
	{
		if(xhr.status === 200)
		{
			$('#remote-image-list').html(xhr.responseText);
			$(".progressbar").css({'display':'none'});
		} 
	};
	xhr.upload.addEventListener("progress", progressHandler, false);
	xhr.addEventListener("load", completeHandler, false);
	xhr.addEventListener("error", errorHandler, false);
	xhr.addEventListener("abort", abortHandler, false);
	xhr.send(formData);
	}
}