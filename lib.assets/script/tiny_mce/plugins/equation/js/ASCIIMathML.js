﻿let asciimath = {};

(
    function() //NOSONAR
{
    let mathcolor = ""; // change it to "" (to inherit) or another color
    //commented let mathfontsize = "1em"; // change to e.g. 1.2em for larger math
    let mathfontfamily = "serif"; // change to "" to inherit (works in IE) 
    // or another family (e.g. "arial")
    //commented let automathrecognize = false; // writing "amath" on page makes this true
    let checkForMathML = true; // check if browser can display MathML
    let notifyIfNoMathML = true; // display note at top if no MathML capability
    let alertIfNoMathML = false; // show alert box if no MathML capability
    //commented let translateOnLoad = true; // set to false to do call translators from js 
    //commented let translateASCIIMath = true; // false to preserve `..`
    let displaystyle = true; // puts limits above and below large operators
    let showasciiformulaonhover = true; // helps students learn ASCIIMath
    let decimalsign = "."; // change to "," if you like, beware of `(1,2)`!
    let AMdelimiter1 = "`";
    //commented let AMescape1 = "\\\\`"; // can use other characters
    //commented let AMdocumentId = "wikitext" // PmWiki element containing math (default=body)
    let fixphi = true; //false to return to legacy phi/varphi mapping

    /*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

    let isIE = (navigator.appName.slice(0, 9) == "Microsoft"); //NOSONAR
    let noMathML = false;
    //commented let translated = false;

    if (isIE) { // add MathPlayer info to IE webpages
        document.write("<object id=\"mathplayer\" classid=\"clsid:32F66A20-7614-11D4-BD11-00104BD3F987\"></object>"); //NOSONAR
        document.write("<?import namespace=\"m\" implementation=\"#mathplayer\"?>");
    }

    // Add a stylesheet, replacing any previous custom stylesheet (adapted from TW)
    function setStylesheet(s) {
        let id = "AMMLcustomStyleSheet";
        let n = document.getElementById(id);
        if (document.createStyleSheet) {
            // Test for IE's non-standard createStyleSheet method
            if (n)
                n.parentNode.removeChild(n);
            // This failed without the &nbsp;
            document.getElementsByTagName("head")[0].insertAdjacentHTML("beforeEnd", "&nbsp;<style id='" + id + "'>" + s + "</style>");
        } else {
            if (n) {
                n.replaceChild(document.createTextNode(s), n.firstChild);
            } else {
                n = document.createElement("style");
                n.type = "text/css";
                n.id = id;
                n.appendChild(document.createTextNode(s));
                document.getElementsByTagName("head")[0].appendChild(n);
            }
        }
    }

    setStylesheet("#AMMLcloseDiv \{font-size:0.8em; padding-top:1em; color:#014\}\n#AMMLwarningBox \{position:absolute; width:100%; top:0; left:0; z-index:200; text-align:center; font-size:1em; font-weight:bold; padding:0.5em 0 0.5em 0; color:#ffc; background:#c30\}");

    function init() {
        let msg;
        let warnings = []; //NOSONAR
        if (checkForMathML && (msg = checkMathML())) 
        {
            warnings.push(msg);
        }
        if (!noMathML) 
        {
            initSymbols();
        }
        return true;
    }

    function checkMathML() {
        if (navigator.appName.slice(0, 8) == "Netscape") //NOSONAR
        {
            if (navigator.appVersion.slice(0, 1) >= "5")  //NOSONAR
            {
                noMathML = null;
            }
            else 
            {
                noMathML = true;
            }
        }
        else if (navigator.appName.slice(0, 9) == "Microsoft")
        {
            try 
            {
                let ActiveX = new ActiveXObject("MathPlayer.Factory.1"); //NOSONAR
                noMathML = null;
            } catch (e) {
                noMathML = true;
            }
        }
        else if (navigator.appName.slice(0, 5) == "Opera") //NOSONAR
        {
            if (navigator.appVersion.slice(0, 3) >= "9.5") //NOSONAR 
            {
                noMathML = null;
            }
            else 
            {
                noMathML = true;
            }
        }
        //commented noMathML = true; //uncomment to check
        if (noMathML && notifyIfNoMathML) {
            let msg = "To view the ASCIIMathML notation use Internet Explorer + MathPlayer or Mozilla Firefox 2.0 or later.";
            if (alertIfNoMathML)
			{
                console.log(msg);
			}
            else 
            {
                return msg;
            }
        }
    }

    function createElementXHTML(t) {
        if (isIE) 
        {
            return document.createElement(t);
        }
        else 
        {
            return document.createElementNS("http://www.w3.org/1999/xhtml", t);
        }
    }

    let AMmathml = "http://www.w3.org/1998/Math/MathML";

    function AMcreateElementMathML(t) //NOSONAR
    {
        if (isIE)
        {
            return document.createElement("m:" + t);
        }
        else 
        {
            return document.createElementNS(AMmathml, t);
        }
    }

    function createMmlNode(t, frag) 
    {
        let node;
        if (isIE) 
        {
            node = document.createElement("m:" + t);
        }
        else 
        {
            node = document.createElementNS(AMmathml, t);
        }
        if(frag) 
        {
            node.appendChild(frag);
        }
        return node;
    }
    function createRootNode(mi, mn) //NOSONAR
    {
		let t = 'mroot';
        let node;
        if (isIE) 
        {
            node = document.createElement("m:" + t);
        }
        else 
        {
            node = document.createElementNS(AMmathml, t);
        }
        if(mi)
        {
			node.appendChild(mi);
		}
		if(mn)
        {
			node.appendChild(mn);
		}
        return node;
    }

    function newcommand(oldstr, newstr) 
    {
        AMsymbols.push({
            input: oldstr,
            tag: "mo",
            output: newstr,
            tex: null,
            ttype: DEFINITION
        });
        refreshSymbols();
    }

    function newsymbol(symbolobj) {
        AMsymbols.push(symbolobj);
        refreshSymbols();
    }

    // character lists for Mozilla/Netscape fonts
    let AMcal = ["\uD835\uDC9C", "\u212C", "\uD835\uDC9E", "\uD835\uDC9F", "\u2130", "\u2131", "\uD835\uDCA2", "\u210B", "\u2110", "\uD835\uDCA5", "\uD835\uDCA6", "\u2112", "\u2133", "\uD835\uDCA9", "\uD835\uDCAA", "\uD835\uDCAB", "\uD835\uDCAC", "\u211B", "\uD835\uDCAE", "\uD835\uDCAF", "\uD835\uDCB0", "\uD835\uDCB1", "\uD835\uDCB2", "\uD835\uDCB3", "\uD835\uDCB4", "\uD835\uDCB5", "\uD835\uDCB6", "\uD835\uDCB7", "\uD835\uDCB8", "\uD835\uDCB9", "\u212F", "\uD835\uDCBB", "\u210A", "\uD835\uDCBD", "\uD835\uDCBE", "\uD835\uDCBF", "\uD835\uDCC0", "\uD835\uDCC1", "\uD835\uDCC2", "\uD835\uDCC3", "\u2134", "\uD835\uDCC5", "\uD835\uDCC6", "\uD835\uDCC7", "\uD835\uDCC8", "\uD835\uDCC9", "\uD835\uDCCA", "\uD835\uDCCB", "\uD835\uDCCC", "\uD835\uDCCD", "\uD835\uDCCE", "\uD835\uDCCF"];
    let AMfrk = ["\uD835\uDD04", "\uD835\uDD05", "\u212D", "\uD835\uDD07", "\uD835\uDD08", "\uD835\uDD09", "\uD835\uDD0A", "\u210C", "\u2111", "\uD835\uDD0D", "\uD835\uDD0E", "\uD835\uDD0F", "\uD835\uDD10", "\uD835\uDD11", "\uD835\uDD12", "\uD835\uDD13", "\uD835\uDD14", "\u211C", "\uD835\uDD16", "\uD835\uDD17", "\uD835\uDD18", "\uD835\uDD19", "\uD835\uDD1A", "\uD835\uDD1B", "\uD835\uDD1C", "\u2128", "\uD835\uDD1E", "\uD835\uDD1F", "\uD835\uDD20", "\uD835\uDD21", "\uD835\uDD22", "\uD835\uDD23", "\uD835\uDD24", "\uD835\uDD25", "\uD835\uDD26", "\uD835\uDD27", "\uD835\uDD28", "\uD835\uDD29", "\uD835\uDD2A", "\uD835\uDD2B", "\uD835\uDD2C", "\uD835\uDD2D", "\uD835\uDD2E", "\uD835\uDD2F", "\uD835\uDD30", "\uD835\uDD31", "\uD835\uDD32", "\uD835\uDD33", "\uD835\uDD34", "\uD835\uDD35", "\uD835\uDD36", "\uD835\uDD37"];
    let AMbbb = ["\uD835\uDD38", "\uD835\uDD39", "\u2102", "\uD835\uDD3B", "\uD835\uDD3C", "\uD835\uDD3D", "\uD835\uDD3E", "\u210D", "\uD835\uDD40", "\uD835\uDD41", "\uD835\uDD42", "\uD835\uDD43", "\uD835\uDD44", "\u2115", "\uD835\uDD46", "\u2119", "\u211A", "\u211D", "\uD835\uDD4A", "\uD835\uDD4B", "\uD835\uDD4C", "\uD835\uDD4D", "\uD835\uDD4E", "\uD835\uDD4F", "\uD835\uDD50", "\u2124", "\uD835\uDD52", "\uD835\uDD53", "\uD835\uDD54", "\uD835\uDD55", "\uD835\uDD56", "\uD835\uDD57", "\uD835\uDD58", "\uD835\uDD59", "\uD835\uDD5A", "\uD835\uDD5B", "\uD835\uDD5C", "\uD835\uDD5D", "\uD835\uDD5E", "\uD835\uDD5F", "\uD835\uDD60", "\uD835\uDD61", "\uD835\uDD62", "\uD835\uDD63", "\uD835\uDD64", "\uD835\uDD65", "\uD835\uDD66", "\uD835\uDD67", "\uD835\uDD68", "\uD835\uDD69", "\uD835\uDD6A", "\uD835\uDD6B"];
    /** commented
     * let AMcal = [0xEF35,0x212C,0xEF36,0xEF37,0x2130,0x2131,0xEF38,0x210B,0x2110,0xEF39,0xEF3A,0x2112,0x2133,0xEF3B,0xEF3C,0xEF3D,0xEF3E,0x211B,0xEF3F,0xEF40,0xEF41,0xEF42,0xEF43,0xEF44,0xEF45,0xEF46];
     * let AMfrk = [0xEF5D,0xEF5E,0x212D,0xEF5F,0xEF60,0xEF61,0xEF62,0x210C,0x2111,0xEF63,0xEF64,0xEF65,0xEF66,0xEF67,0xEF68,0xEF69,0xEF6A,0x211C,0xEF6B,0xEF6C,0xEF6D,0xEF6E,0xEF6F,0xEF70,0xEF71,0x2128];
     * let AMbbb = [0xEF8C,0xEF8D,0x2102,0xEF8E,0xEF8F,0xEF90,0xEF91,0x210D,0xEF92,0xEF93,0xEF94,0xEF95,0xEF96,0x2115,0xEF97,0x2119,0x211A,0x211D,0xEF98,0xEF99,0xEF9A,0xEF9B,0xEF9C,0xEF9D,0xEF9E,0x2124];
     * */

    let CONST = 0,
        UNARY = 1,
        BINARY = 2,
        INFIX = 3,
        LEFTBRACKET = 4,
        RIGHTBRACKET = 5,
        SPACE = 6,
        UNDEROVER = 7,
        DEFINITION = 8,
        LEFTRIGHT = 9,
        TEXT = 10,
        BIG = 11,
        LONG = 12,
        STRETCHY = 13,
        MATRIX = 14,
        UNARYUNDEROVER = 15; // token types

    let AMquote = {
        input: "\"",
        tag: "mtext",
        output: "mbox",
        tex: null,
        ttype: TEXT
    };

    let AMsymbols = [
        //some greek symbols
        {
            input: "alpha",
            tag: "mi",
            output: "\u03B1",
            tex: null,
            ttype: CONST
        },
        {
            input: "beta",
            tag: "mi",
            output: "\u03B2",
            tex: null,
            ttype: CONST
        },
        {
            input: "chi",
            tag: "mi",
            output: "\u03C7",
            tex: null,
            ttype: CONST
        },
        {
            input: "delta",
            tag: "mi",
            output: "\u03B4",
            tex: null,
            ttype: CONST
        },
        {
            input: "Delta",
            tag: "mo",
            output: "\u0394",
            tex: null,
            ttype: CONST
        },
        {
            input: "epsi",
            tag: "mi",
            output: "\u03B5",
            tex: "epsilon",
            ttype: CONST
        },
        {
            input: "varepsilon",
            tag: "mi",
            output: "\u025B",
            tex: null,
            ttype: CONST
        },
        {
            input: "eta",
            tag: "mi",
            output: "\u03B7",
            tex: null,
            ttype: CONST
        },
        {
            input: "gamma",
            tag: "mi",
            output: "\u03B3",
            tex: null,
            ttype: CONST
        },
        {
            input: "Gamma",
            tag: "mo",
            output: "\u0393",
            tex: null,
            ttype: CONST
        },
        {
            input: "iota",
            tag: "mi",
            output: "\u03B9",
            tex: null,
            ttype: CONST
        },
        {
            input: "kappa",
            tag: "mi",
            output: "\u03BA",
            tex: null,
            ttype: CONST
        },
        {
            input: "lambda",
            tag: "mi",
            output: "\u03BB",
            tex: null,
            ttype: CONST
        },
        {
            input: "Lambda",
            tag: "mo",
            output: "\u039B",
            tex: null,
            ttype: CONST
        },
        {
            input: "lamda",
            tag: "mi",
            output: "\u03BB",
            tex: null,
            ttype: CONST
        },
        {
            input: "Lamda",
            tag: "mo",
            output: "\u039B",
            tex: null,
            ttype: CONST
        },
        {
            input: "mu",
            tag: "mi",
            output: "\u03BC",
            tex: null,
            ttype: CONST
        },
        {
            input: "nu",
            tag: "mi",
            output: "\u03BD",
            tex: null,
            ttype: CONST
        },
        {
            input: "omega",
            tag: "mi",
            output: "\u03C9",
            tex: null,
            ttype: CONST
        },
        {
            input: "Omega",
            tag: "mo",
            output: "\u03A9",
            tex: null,
            ttype: CONST
        },
        {
            input: "phi",
            tag: "mi",
            output: fixphi ? "\u03D5" : "\u03C6",
            tex: null,
            ttype: CONST
        },
        {
            input: "varphi",
            tag: "mi",
            output: fixphi ? "\u03C6" : "\u03D5",
            tex: null,
            ttype: CONST
        },
        {
            input: "Phi",
            tag: "mo",
            output: "\u03A6",
            tex: null,
            ttype: CONST
        },
        {
            input: "pi",
            tag: "mi",
            output: "\u03C0",
            tex: null,
            ttype: CONST
        },
        {
            input: "Pi",
            tag: "mo",
            output: "\u03A0",
            tex: null,
            ttype: CONST
        },
        {
            input: "psi",
            tag: "mi",
            output: "\u03C8",
            tex: null,
            ttype: CONST
        },
        {
            input: "Psi",
            tag: "mi",
            output: "\u03A8",
            tex: null,
            ttype: CONST
        },
        {
            input: "rho",
            tag: "mi",
            output: "\u03C1",
            tex: null,
            ttype: CONST
        },
        {
            input: "sigma",
            tag: "mi",
            output: "\u03C3",
            tex: null,
            ttype: CONST
        },
        {
            input: "Sigma",
            tag: "mo",
            output: "\u03A3",
            tex: null,
            ttype: CONST
        },
        {
            input: "tau",
            tag: "mi",
            output: "\u03C4",
            tex: null,
            ttype: CONST
        },
        {
            input: "theta",
            tag: "mi",
            output: "\u03B8",
            tex: null,
            ttype: CONST
        },
        {
            input: "vartheta",
            tag: "mi",
            output: "\u03D1",
            tex: null,
            ttype: CONST
        },
        {
            input: "Theta",
            tag: "mo",
            output: "\u0398",
            tex: null,
            ttype: CONST
        },
        {
            input: "upsilon",
            tag: "mi",
            output: "\u03C5",
            tex: null,
            ttype: CONST
        },
        {
            input: "xi",
            tag: "mi",
            output: "\u03BE",
            tex: null,
            ttype: CONST
        },
        {
            input: "Xi",
            tag: "mo",
            output: "\u039E",
            tex: null,
            ttype: CONST
        },
        {
            input: "zeta",
            tag: "mi",
            output: "\u03B6",
            tex: null,
            ttype: CONST
        },

        //binary operation symbols
        //{input:"-",  tag:"mo", output:"\u0096", tex:null, ttype:CONST},
        {
            input: "*",
            tag: "mo",
            output: "\u22C5",
            tex: "cdot",
            ttype: CONST
        },
        {
            input: "**",
            tag: "mo",
            output: "\u2217",
            tex: "ast",
            ttype: CONST
        },
        {
            input: "***",
            tag: "mo",
            output: "\u22C6",
            tex: "star",
            ttype: CONST
        },
        {
            input: "//",
            tag: "mo",
            output: "/",
            tex: null,
            ttype: CONST
        },
        {
            input: "\\\\",
            tag: "mo",
            output: "\\",
            tex: "backslash",
            ttype: CONST
        },
        {
            input: "setminus",
            tag: "mo",
            output: "\\",
            tex: null,
            ttype: CONST
        },
        {
            input: "xx",
            tag: "mo",
            output: "\u00D7",
            tex: "times",
            ttype: CONST
        },
        {
            input: "|><",
            tag: "mo",
            output: "\u22C9",
            tex: "ltimes",
            ttype: CONST
        },
        {
            input: "><|",
            tag: "mo",
            output: "\u22CA",
            tex: "rtimes",
            ttype: CONST
        },
        {
            input: "|><|",
            tag: "mo",
            output: "\u22C8",
            tex: "bowtie",
            ttype: CONST
        },
        {
            input: "-:",
            tag: "mo",
            output: "\u00F7",
            tex: "div",
            ttype: CONST
        },
        {
            input: "divide",
            tag: "mo",
            output: "-:",
            tex: null,
            ttype: DEFINITION
        },
        {
            input: "@",
            tag: "mo",
            output: "\u2218",
            tex: "circ",
            ttype: CONST
        },
        {
            input: "o+",
            tag: "mo",
            output: "\u2295",
            tex: "oplus",
            ttype: CONST
        },
        {
            input: "ox",
            tag: "mo",
            output: "\u2297",
            tex: "otimes",
            ttype: CONST
        },
        {
            input: "o.",
            tag: "mo",
            output: "\u2299",
            tex: "odot",
            ttype: CONST
        },
        {
            input: "sum",
            tag: "mo",
            output: "\u2211",
            tex: null,
            ttype: UNDEROVER
        },
        {
            input: "prod",
            tag: "mo",
            output: "\u220F",
            tex: null,
            ttype: UNDEROVER
        },
        {
            input: "^^",
            tag: "mo",
            output: "\u2227",
            tex: "wedge",
            ttype: CONST
        },
        {
            input: "^^^",
            tag: "mo",
            output: "\u22C0",
            tex: "bigwedge",
            ttype: UNDEROVER
        },
        {
            input: "vv",
            tag: "mo",
            output: "\u2228",
            tex: "vee",
            ttype: CONST
        },
        {
            input: "vvv",
            tag: "mo",
            output: "\u22C1",
            tex: "bigvee",
            ttype: UNDEROVER
        },
        {
            input: "nn",
            tag: "mo",
            output: "\u2229",
            tex: "cap",
            ttype: CONST
        },
        {
            input: "nnn",
            tag: "mo",
            output: "\u22C2",
            tex: "bigcap",
            ttype: UNDEROVER
        },
        {
            input: "uu",
            tag: "mo",
            output: "\u222A",
            tex: "cup",
            ttype: CONST
        },
        {
            input: "uuu",
            tag: "mo",
            output: "\u22C3",
            tex: "bigcup",
            ttype: UNDEROVER
        },

        //binary relation symbols
        {
            input: "!=",
            tag: "mo",
            output: "\u2260",
            tex: "ne",
            ttype: CONST
        },
        {
            input: ":=",
            tag: "mo",
            output: ":=",
            tex: null,
            ttype: CONST
        },
        {
            input: "lt",
            tag: "mo",
            output: "<",
            tex: null,
            ttype: CONST
        },
        {
            input: "<=",
            tag: "mo",
            output: "\u2264",
            tex: "le",
            ttype: CONST
        },
        {
            input: "lt=",
            tag: "mo",
            output: "\u2264",
            tex: "leq",
            ttype: CONST
        },
        {
            input: "gt",
            tag: "mo",
            output: ">",
            tex: null,
            ttype: CONST
        },
        {
            input: ">=",
            tag: "mo",
            output: "\u2265",
            tex: "ge",
            ttype: CONST
        },
        {
            input: "gt=",
            tag: "mo",
            output: "\u2265",
            tex: "geq",
            ttype: CONST
        },
        {
            input: "-<",
            tag: "mo",
            output: "\u227A",
            tex: "prec",
            ttype: CONST
        },
        {
            input: "-lt",
            tag: "mo",
            output: "\u227A",
            tex: null,
            ttype: CONST
        },
        {
            input: ">-",
            tag: "mo",
            output: "\u227B",
            tex: "succ",
            ttype: CONST
        },
        {
            input: "-<=",
            tag: "mo",
            output: "\u2AAF",
            tex: "preceq",
            ttype: CONST
        },
        {
            input: ">-=",
            tag: "mo",
            output: "\u2AB0",
            tex: "succeq",
            ttype: CONST
        },
        {
            input: "in",
            tag: "mo",
            output: "\u2208",
            tex: null,
            ttype: CONST
        },
        {
            input: "!in",
            tag: "mo",
            output: "\u2209",
            tex: "notin",
            ttype: CONST
        },
        {
            input: "sub",
            tag: "mo",
            output: "\u2282",
            tex: "subset",
            ttype: CONST
        },
        {
            input: "sup",
            tag: "mo",
            output: "\u2283",
            tex: "supset",
            ttype: CONST
        },
        {
            input: "sube",
            tag: "mo",
            output: "\u2286",
            tex: "subseteq",
            ttype: CONST
        },
        {
            input: "supe",
            tag: "mo",
            output: "\u2287",
            tex: "supseteq",
            ttype: CONST
        },
        {
            input: "-=",
            tag: "mo",
            output: "\u2261",
            tex: "equiv",
            ttype: CONST
        },
        {
            input: "~=",
            tag: "mo",
            output: "\u2245",
            tex: "cong",
            ttype: CONST
        },
        {
            input: "~~",
            tag: "mo",
            output: "\u2248",
            tex: "approx",
            ttype: CONST
        },
        {
            input: "prop",
            tag: "mo",
            output: "\u221D",
            tex: "propto",
            ttype: CONST
        },

        //logical symbols
        {
            input: "and",
            tag: "mtext",
            output: "and",
            tex: null,
            ttype: SPACE
        },
        {
            input: "or",
            tag: "mtext",
            output: "or",
            tex: null,
            ttype: SPACE
        },
        {
            input: "not",
            tag: "mo",
            output: "\u00AC",
            tex: "neg",
            ttype: CONST
        },
        {
            input: "=>",
            tag: "mo",
            output: "\u21D2",
            tex: "implies",
            ttype: CONST
        },
        {
            input: "if",
            tag: "mo",
            output: "if",
            tex: null,
            ttype: SPACE
        },
        {
            input: "<=>",
            tag: "mo",
            output: "\u21D4",
            tex: "iff",
            ttype: CONST
        },
        {
            input: "AA",
            tag: "mo",
            output: "\u2200",
            tex: "forall",
            ttype: CONST
        },
        {
            input: "EE",
            tag: "mo",
            output: "\u2203",
            tex: "exists",
            ttype: CONST
        },
        {
            input: "_|_",
            tag: "mo",
            output: "\u22A5",
            tex: "bot",
            ttype: CONST
        },
        {
            input: "TT",
            tag: "mo",
            output: "\u22A4",
            tex: "top",
            ttype: CONST
        },
        {
            input: "|--",
            tag: "mo",
            output: "\u22A2",
            tex: "vdash",
            ttype: CONST
        },
        {
            input: "|==",
            tag: "mo",
            output: "\u22A8",
            tex: "models",
            ttype: CONST
        },

        //grouping brackets
        {
            input: "(",
            tag: "mo",
            output: "(",
            tex: null,
            ttype: LEFTBRACKET
        },
        {
            input: ")",
            tag: "mo",
            output: ")",
            tex: null,
            ttype: RIGHTBRACKET
        },
        {
            input: "[",
            tag: "mo",
            output: "[",
            tex: null,
            ttype: LEFTBRACKET
        },
        {
            input: "]",
            tag: "mo",
            output: "]",
            tex: null,
            ttype: RIGHTBRACKET
        },
        {
            input: "{",
            tag: "mo",
            output: "{",
            tex: null,
            ttype: LEFTBRACKET
        },
        {
            input: "}",
            tag: "mo",
            output: "}",
            tex: null,
            ttype: RIGHTBRACKET
        },
        {
            input: "|",
            tag: "mo",
            output: "|",
            tex: null,
            ttype: LEFTRIGHT
        },
        //{input:"||", tag:"mo", output:"||", tex:null, ttype:LEFTRIGHT},
        {
            input: "(:",
            tag: "mo",
            output: "\u2329",
            tex: "langle",
            ttype: LEFTBRACKET
        },
        {
            input: ":)",
            tag: "mo",
            output: "\u232A",
            tex: "rangle",
            ttype: RIGHTBRACKET
        },
        {
            input: "<<",
            tag: "mo",
            output: "\u2329",
            tex: null,
            ttype: LEFTBRACKET
        },
        {
            input: ">>",
            tag: "mo",
            output: "\u232A",
            tex: null,
            ttype: RIGHTBRACKET
        },
        {
            input: "{:",
            tag: "mo",
            output: "{:",
            tex: null,
            ttype: LEFTBRACKET,
            invisible: true
        },
        {
            input: ":}",
            tag: "mo",
            output: ":}",
            tex: null,
            ttype: RIGHTBRACKET,
            invisible: true
        },

        //miscellaneous symbols
        {
            input: "iiiint",
            tag: "mo",
            output: "\u2A0C",
            tex: null,
            ttype: CONST
        },
        {
            input: "oiiint",
            tag: "mo",
            output: "\u2230",
            tex: null,
            ttype: CONST
        },
        {
            input: "iiint",
            tag: "mo",
            output: "\u222D",
            tex: null,
            ttype: CONST
        },
        {
            input: "oiint",
            tag: "mo",
            output: "\u222F",
            tex: null,
            ttype: CONST
        },
        {
            input: "iint",
            tag: "mo",
            output: "\u222C",
            tex: null,
            ttype: CONST
        },
        {
            input: "oint",
            tag: "mo",
            output: "\u222E",
            tex: null,
            ttype: CONST
        },
        {
            input: "int",
            tag: "mo",
            output: "\u222B",
            tex: null,
            ttype: CONST
        },
        {
            input: "dx",
            tag: "mi",
            output: "{:d x:}",
            tex: null,
            ttype: DEFINITION
        },
        {
            input: "dy",
            tag: "mi",
            output: "{:d y:}",
            tex: null,
            ttype: DEFINITION
        },
        {
            input: "dz",
            tag: "mi",
            output: "{:d z:}",
            tex: null,
            ttype: DEFINITION
        },
        {
            input: "dt",
            tag: "mi",
            output: "{:d t:}",
            tex: null,
            ttype: DEFINITION
        },
        {
            input: "del",
            tag: "mo",
            output: "\u2202",
            tex: "partial",
            ttype: CONST
        },
        {
            input: "grad",
            tag: "mo",
            output: "\u2207",
            tex: "nabla",
            ttype: CONST
        },
        {
            input: "+-",
            tag: "mo",
            output: "\u00B1",
            tex: "pm",
            ttype: CONST
        },
        {
            input: "O/",
            tag: "mo",
            output: "\u2205",
            tex: "emptyset",
            ttype: CONST
        },
        {
            input: "oo",
            tag: "mo",
            output: "\u221E",
            tex: "infty",
            ttype: CONST
        },
        {
            input: "aleph",
            tag: "mo",
            output: "\u2135",
            tex: null,
            ttype: CONST
        },
        {
            input: "...",
            tag: "mo",
            output: "...",
            tex: "ldots",
            ttype: CONST
        },
        {
            input: ":.",
            tag: "mo",
            output: "\u2234",
            tex: "therefore",
            ttype: CONST
        },
        {
            input: ":'",
            tag: "mo",
            output: "\u2235",
            tex: "because",
            ttype: CONST
        },
        {
            input: "/_",
            tag: "mo",
            output: "\u2220",
            tex: "angle",
            ttype: CONST
        },
        {
            input: "/_\\",
            tag: "mo",
            output: "\u25B3",
            tex: "triangle",
            ttype: CONST
        },
        {
            input: "'",
            tag: "mo",
            output: "\u2032",
            tex: "prime",
            ttype: CONST
        },
        {
            input: "tilde",
            tag: "mover",
            output: "~",
            tex: null,
            ttype: UNARY,
            acc: true
        },
        {
            input: "\\ ",
            tag: "mo",
            output: "\u00A0",
            tex: null,
            ttype: CONST
        },
        {
            input: "frown",
            tag: "mo",
            output: "\u2322",
            tex: null,
            ttype: CONST
        },
        {
            input: "quad",
            tag: "mo",
            output: "\u00A0\u00A0",
            tex: null,
            ttype: CONST
        },
        {
            input: "qquad",
            tag: "mo",
            output: "\u00A0\u00A0\u00A0\u00A0",
            tex: null,
            ttype: CONST
        },
        {
            input: "cdots",
            tag: "mo",
            output: "\u22EF",
            tex: null,
            ttype: CONST
        },
        {
            input: "vdots",
            tag: "mo",
            output: "\u22EE",
            tex: null,
            ttype: CONST
        },
        {
            input: "ddots",
            tag: "mo",
            output: "\u22F1",
            tex: null,
            ttype: CONST
        },
        {
            input: "diamond",
            tag: "mo",
            output: "\u22C4",
            tex: null,
            ttype: CONST
        },
        {
            input: "square",
            tag: "mo",
            output: "\u25A1",
            tex: null,
            ttype: CONST
        },
        {
            input: "|__",
            tag: "mo",
            output: "\u230A",
            tex: "lfloor",
            ttype: CONST
        },
        {
            input: "__|",
            tag: "mo",
            output: "\u230B",
            tex: "rfloor",
            ttype: CONST
        },
        {
            input: "|~",
            tag: "mo",
            output: "\u2308",
            tex: "lceiling",
            ttype: CONST
        },
        {
            input: "~|",
            tag: "mo",
            output: "\u2309",
            tex: "rceiling",
            ttype: CONST
        },
        {
            input: "CC",
            tag: "mo",
            output: "\u2102",
            tex: null,
            ttype: CONST
        },
        {
            input: "NN",
            tag: "mo",
            output: "\u2115",
            tex: null,
            ttype: CONST
        },
        {
            input: "QQ",
            tag: "mo",
            output: "\u211A",
            tex: null,
            ttype: CONST
        },
        {
            input: "RR",
            tag: "mo",
            output: "\u211D",
            tex: null,
            ttype: CONST
        },
        {
            input: "ZZ",
            tag: "mo",
            output: "\u2124",
            tex: null,
            ttype: CONST
        },
        {
            input: "f",
            tag: "mi",
            output: "f",
            tex: null,
            ttype: UNARY,
            func: true
        },
        {
            input: "g",
            tag: "mi",
            output: "g",
            tex: null,
            ttype: UNARY,
            func: true
        },

        //standard functions
        {
            input: "lim",
            tag: "mo",
            output: "lim",
            tex: null,
            ttype: UNDEROVER
        },
        {
            input: "Lim",
            tag: "mo",
            output: "Lim",
            tex: null,
            ttype: UNDEROVER
        },
        {
            input: "sin",
            tag: "mo",
            output: "sin",
            tex: null,
            ttype: UNARY,
            func: true
        },
        {
            input: "cos",
            tag: "mo",
            output: "cos",
            tex: null,
            ttype: UNARY,
            func: true
        },
        {
            input: "tan",
            tag: "mo",
            output: "tan",
            tex: null,
            ttype: UNARY,
            func: true
        },
        {
            input: "sinh",
            tag: "mo",
            output: "sinh",
            tex: null,
            ttype: UNARY,
            func: true
        },
        {
            input: "cosh",
            tag: "mo",
            output: "cosh",
            tex: null,
            ttype: UNARY,
            func: true
        },
        {
            input: "tanh",
            tag: "mo",
            output: "tanh",
            tex: null,
            ttype: UNARY,
            func: true
        },
        {
            input: "cot",
            tag: "mo",
            output: "cot",
            tex: null,
            ttype: UNARY,
            func: true
        },
        {
            input: "sec",
            tag: "mo",
            output: "sec",
            tex: null,
            ttype: UNARY,
            func: true
        },
        {
            input: "csc",
            tag: "mo",
            output: "csc",
            tex: null,
            ttype: UNARY,
            func: true
        },
        {
            input: "arcsin",
            tag: "mo",
            output: "arcsin",
            tex: null,
            ttype: UNARY,
            func: true
        },
        {
            input: "arccos",
            tag: "mo",
            output: "arccos",
            tex: null,
            ttype: UNARY,
            func: true
        },
        {
            input: "arctan",
            tag: "mo",
            output: "arctan",
            tex: null,
            ttype: UNARY,
            func: true
        },
        {
            input: "coth",
            tag: "mo",
            output: "coth",
            tex: null,
            ttype: UNARY,
            func: true
        },
        {
            input: "sech",
            tag: "mo",
            output: "sech",
            tex: null,
            ttype: UNARY,
            func: true
        },
        {
            input: "csch",
            tag: "mo",
            output: "csch",
            tex: null,
            ttype: UNARY,
            func: true
        },
        {
            input: "exp",
            tag: "mo",
            output: "exp",
            tex: null,
            ttype: UNARY,
            func: true
        },
        {
            input: "abs",
            tag: "mo",
            output: "abs",
            tex: null,
            ttype: UNARY,
            rewriteleftright: ["|", "|"]
        },
        {
            input: "norm",
            tag: "mo",
            output: "norm",
            tex: null,


            ttype: UNARY,
            rewriteleftright: ["\u2225", "\u2225"]
        },
        {
            input: "floor",
            tag: "mo",
            output: "floor",
            tex: null,
            ttype: UNARY,
            rewriteleftright: ["\u230A", "\u230B"]
        },
        {
            input: "ceil",
            tag: "mo",
            output: "ceil",
            tex: null,
            ttype: UNARY,
            rewriteleftright: ["\u2308", "\u2309"]
        },
        {
            input: "log",
            tag: "mo",
            output: "log",
            tex: null,
            ttype: UNARY,
            func: true
        },

        {
            input: "ln",
            tag: "mo",
            output: "ln",
            tex: null,
            ttype: UNARY,
            func: true
        },
        {
            input: "det",
            tag: "mo",
            output: "det",
            tex: null,
            ttype: UNARY,
            func: true
        },
        {
            input: "dim",
            tag: "mo",
            output: "dim",
            tex: null,
            ttype: CONST
        },
        {
            input: "mod",
            tag: "mo",
            output: "mod",
            tex: null,
            ttype: CONST
        },
        {
            input: "gcd",
            tag: "mo",
            output: "gcd",
            tex: null,
            ttype: UNARY,
            func: true
        },
        {
            input: "lcm",
            tag: "mo",
            output: "lcm",
            tex: null,
            ttype: UNARY,
            func: true
        },
        {
            input: "lub",
            tag: "mo",
            output: "lub",
            tex: null,
            ttype: CONST
        },
        {
            input: "glb",
            tag: "mo",
            output: "glb",
            tex: null,
            ttype: CONST
        },
        {
            input: "min",
            tag: "mo",
            output: "min",
            tex: null,
            ttype: UNDEROVER
        },
        {
            input: "max",
            tag: "mo",
            output: "max",
            tex: null,
            ttype: UNDEROVER
        },

        //arrows
        {
            input: "uarr",
            tag: "mo",
            output: "\u2191",
            tex: "uparrow",
            ttype: CONST
        },
        {
            input: "darr",
            tag: "mo",
            output: "\u2193",
            tex: "downarrow",
            ttype: CONST
        },
        {
            input: "rarr",
            tag: "mo",
            output: "\u2192",
            tex: "rightarrow",
            ttype: CONST
        },
        {
            input: "->",
            tag: "mo",
            output: "\u2192",
            tex: "to",
            ttype: CONST
        },
        {
            input: ">->",
            tag: "mo",
            output: "\u21A3",
            tex: "rightarrowtail",
            ttype: CONST
        },
        {
            input: "->>",
            tag: "mo",
            output: "\u21A0",
            tex: "twoheadrightarrow",
            ttype: CONST
        },
        {
            input: ">->>",
            tag: "mo",
            output: "\u2916",
            tex: "twoheadrightarrowtail",
            ttype: CONST
        },
        {
            input: "|->",
            tag: "mo",
            output: "\u21A6",
            tex: "mapsto",
            ttype: CONST
        },
        {
            input: "larr",
            tag: "mo",
            output: "\u2190",
            tex: "leftarrow",
            ttype: CONST
        },
        {
            input: "harr",
            tag: "mo",
            output: "\u2194",
            tex: "leftrightarrow",
            ttype: CONST
        },
        {
            input: "rArr",
            tag: "mo",
            output: "\u21D2",
            tex: "Rightarrow",
            ttype: CONST
        },
        {
            input: "lArr",
            tag: "mo",
            output: "\u21D0",
            tex: "Leftarrow",
            ttype: CONST
        },
        {
            input: "hArr",
            tag: "mo",
            output: "\u21D4",
            tex: "Leftrightarrow",
            ttype: CONST
        },
        //commands with argument
        {
            input: "sqrt",
            tag: "msqrt",
            output: "sqrt",
            tex: null,
            ttype: UNARY
        },
        {
            input: "root",
            tag: "mroot",
            output: "root",
            tex: null,
            ttype: BINARY
        },
        {
            input: "frac",
            tag: "mfrac",
            output: "/",
            tex: null,
            ttype: BINARY
        },
        {
            input: "/",
            tag: "mfrac",
            output: "/",
            tex: null,
            ttype: INFIX
        },
        {
            input: "stackrel",
            tag: "mover",
            output: "stackrel",
            tex: null,
            ttype: BINARY
        },
        {
            input: "overset",
            tag: "mover",
            output: "stackrel",
            tex: null,
            ttype: BINARY
        },
        {
            input: "underset",
            tag: "munder",
            output: "stackrel",
            tex: null,
            ttype: BINARY
        },
        {
            input: "_",
            tag: "msub",
            output: "_",
            tex: null,
            ttype: INFIX
        },
        {
            input: "^",
            tag: "msup",
            output: "^",
            tex: null,
            ttype: INFIX
        },
        {
            input: "hat",
            tag: "mover",
            output: "\u005E",
            tex: null,
            ttype: UNARY,
            acc: true
        },
        {
            input: "bar",
            tag: "mover",
            output: "\u00AF",
            tex: "overline",
            ttype: UNARY,
            acc: true
        },
        {
            input: "vec",
            tag: "mover",
            output: "\u2192",
            tex: null,
            ttype: UNARY,
            acc: true
        },
        {
            input: "dot",
            tag: "mover",
            output: ".",
            tex: null,
            ttype: UNARY,
            acc: true
        },
        {
            input: "ddot",
            tag: "mover",
            output: "..",
            tex: null,
            ttype: UNARY,
            acc: true
        },
        {
            input: "ul",
            tag: "munder",
            output: "\u0332",
            tex: "underline",
            ttype: UNARY,
            acc: true
        },
        {
            input: "ubrace",
            tag: "munder",
            output: "\u23DF",
            tex: "underbrace",
            ttype: UNARYUNDEROVER,
            acc: true
        },
        {
            input: "obrace",
            tag: "mover",
            output: "\u23DE",
            tex: "overbrace",
            ttype: UNARYUNDEROVER,

            acc: true
        },
        {
            input: "text",
            tag: "mtext",
            output: "text",
            tex: null,
            ttype: TEXT
        },
        {
            input: "mbox",
            tag: "mtext",
            output: "mbox",
            tex: null,
            ttype: TEXT
        },
        {
            input: "color",
            tag: "mstyle",
            ttype: BINARY
        },
        {
            input: "cancel",
            tag: "menclose",
            output: "cancel",
            tex: null,
            ttype: UNARY
        },
        AMquote,
        {
            input: "bb",
            tag: "mstyle",
            atname: "mathvariant",
            atval: "bold",
            output: "bb",
            tex: null,
            ttype: UNARY
        },
        {
            input: "mathbf",
            tag: "mstyle",
            atname: "mathvariant",
            atval: "bold",
            output: "mathbf",
            tex: null,
            ttype: UNARY
        },
        {
            input: "sf",
            tag: "mstyle",
            atname: "mathvariant",
            atval: "sans-serif",
            output: "sf",
            tex: null,
            ttype: UNARY
        },
        {
            input: "mathsf",
            tag: "mstyle",
            atname: "mathvariant",
            atval: "sans-serif",
            output: "mathsf",
            tex: null,
            ttype: UNARY
        },
        {
            input: "bbb",
            tag: "mstyle",
            atname: "mathvariant",
            atval: "double-struck",
            output: "bbb",
            tex: null,
            ttype: UNARY,
            codes: AMbbb
        },
        {
            input: "mathbb",
            tag: "mstyle",
            atname: "mathvariant",
            atval: "double-struck",
            output: "mathbb",
            tex: null,
            ttype: UNARY,
            codes: AMbbb
        },
        {
            input: "cc",
            tag: "mstyle",
            atname: "mathvariant",
            atval: "script",
            output: "cc",
            tex: null,
            ttype: UNARY,
            codes: AMcal
        },
        {
            input: "mathcal",
            tag: "mstyle",
            atname: "mathvariant",
            atval: "script",
            output: "mathcal",
            tex: null,
            ttype: UNARY,
            codes: AMcal
        },
        {
            input: "tt",
            tag: "mstyle",
            atname: "mathvariant",
            atval: "monospace",
            output: "tt",
            tex: null,
            ttype: UNARY
        },
        {
            input: "mathtt",
            tag: "mstyle",
            atname: "mathvariant",
            atval: "monospace",
            output: "mathtt",
            tex: null,
            ttype: UNARY
        },
        {
            input: "fr",
            tag: "mstyle",
            atname: "mathvariant",
            atval: "fraktur",
            output: "fr",
            tex: null,
            ttype: UNARY,
            codes: AMfrk
        },
        {
            input: "mathfrak",
            tag: "mstyle",
            atname: "mathvariant",
            atval: "fraktur",
            output: "mathfrak",
            tex: null,
            ttype: UNARY,
            codes: AMfrk
        }
    ];

    function compareNames(s1, s2) {
        if (s1.input > s2.input) return 1
        else return -1;
    }

    let AMnames = []; //list of input symbols

    function initSymbols() {
        let i;
        let symlen = AMsymbols.length;
        for (i = 0; i < symlen; i++) {
            if (AMsymbols[i].tex) {
                AMsymbols.push({
                    input: AMsymbols[i].tex,
                    tag: AMsymbols[i].tag,
                    output: AMsymbols[i].output,
                    ttype: AMsymbols[i].ttype,
                    acc: (AMsymbols[i].acc || false)
                });
            }
        }
        refreshSymbols();
    }

    function refreshSymbols() {
        let i;
        AMsymbols.sort(compareNames);
        for (i = 0; i < AMsymbols.length; i++) AMnames[i] = AMsymbols[i].input;
    }

    function define(oldstr, newstr) //NOSONAR
    {
        AMsymbols.push({
            input: oldstr,
            tag: "mo",
            output: newstr,
            tex: null,
            ttype: DEFINITION
        });
        refreshSymbols(); // this may be a problem if many symbols are defined!
    }

    function AMremoveCharsAndBlanks(str, n) 
    {
        //remove n characters and any following blanks
        let st;
        if (str.charAt(n) == "\\" && str.charAt(n + 1) != "\\" && str.charAt(n + 1) != " ")
        {
            st = str.slice(n + 1);
        }
        else 
        {
            st = str.slice(n);
        }
        let i;
        for (i = 0; i < st.length && st.charCodeAt(i) <= 32; i = i + 1)
        {
            // Do nothing
        }
        return st.slice(i);
    }

    function position(arr, str, n) {
        // return position >=n where str appears or would be inserted
        // assumes arr is sorted
        let i;
        if (n == 0) 
        {
            let h, m;
            n = -1;
            h = arr.length;
            while (n + 1 < h) 
            {
                m = (n + h) >> 1;
                if (arr[m] < str) 
                {
                    n = m;
                }
                else 
                {
                    h = m;
                }
            }
            return h;
        } 
        else
        {
            for (i = n; i < arr.length && arr[i] < str; i++){
                // Do noyhing
            }
        }
        return i; // i=arr.length || arr[i]>=str
    }

    function AMgetSymbol(str) {
        //return maximal initial substring of str that appears in names
        //return null if there is none
        let k = 0; //new pos
        let j = 0; //old pos
        let mk; //match pos
        let st;
        let tagst;
        let match = "";
        let more = true;
        for (let i = 1; i <= str.length && more; i++) {
            st = str.slice(0, i); //initial substring of length i
            j = k;
            k = position(AMnames, st, j);
            if (k < AMnames.length && str.slice(0, AMnames[k].length) == AMnames[k]) {
                match = AMnames[k];
                mk = k;
                i = match.length; //NOSONAR
            }
            more = k < AMnames.length && str.slice(0, AMnames[k].length) >= AMnames[k];
        }
        AMpreviousSymbol = AMcurrentSymbol;
        if (match != "") {
            AMcurrentSymbol = AMsymbols[mk].ttype;
            return AMsymbols[mk];
        }
        // if str[0] is a digit or - return maxsubstring of digits.digits
        AMcurrentSymbol = CONST;
        k = 1;
        st = str.slice(0, 1);
        let integ = true;
        while ("0" <= st && st <= "9" && k <= str.length) {
            st = str.slice(k, k + 1);
            k++;
        }
        if (st == decimalsign) {
            st = str.slice(k, k + 1);
            if ("0" <= st && st <= "9") {
                integ = false;
                k++;
                while ("0" <= st && st <= "9" && k <= str.length) {
                    st = str.slice(k, k + 1);
                    k++;
                }
            }
        }
        if ((integ && k > 1) || k > 2) {
            st = str.slice(0, k - 1);
            tagst = "mn";
        } else {
            k = 2; //NOSONAR
            st = str.slice(0, 1); //take 1 character
            tagst = (("A" > st || st > "Z") && ("a" > st || st > "z") ? "mo" : "mi");
        }
        if (st == "-" && AMpreviousSymbol == INFIX) {
            AMcurrentSymbol = INFIX; //trick "/" into recognizing "-" on second parse
            return {
                input: st,
                tag: tagst,
                output: st,
                ttype: UNARY,
                func: true
            };
        }
        return {
            input: st,
            tag: tagst,
            output: st,
            ttype: CONST
        };
    }

    function AMremoveBrackets(node) {
        let st;
        if (!node.hasChildNodes()) {
            return;
        }
        if (node.firstChild.hasChildNodes() && (node.nodeName == "mrow" || node.nodeName == "M:MROW")) {
            st = node.firstChild.firstChild.nodeValue;
            if (st == "(" || st == "[" || st == "{") node.removeChild(node.firstChild);
        }
        if (node.lastChild.hasChildNodes() && (node.nodeName == "mrow" || node.nodeName == "M:MROW")) {
            st = node.lastChild.firstChild.nodeValue;
            if (st == ")" || st == "]" || st == "}") node.removeChild(node.lastChild);
        }
    }

    /*Parsing ASCII math expressions with the following grammar
    v ::= [A-Za-z] | greek letters | numbers | other constant symbols
    u ::= sqrt | text | bb | other unary symbols for font commands
    b ::= frac | root | stackrel         binary symbols

    l ::= ( | [ | { | (: | {:            left brackets
    r ::= ) | ] | } | :) | :}            right brackets
    S ::= v | lEr | uS | bSS             Simple expression
    I ::= S_S | S^S | S_S^S | S          Intermediate expression
    E ::= IE | I/I                       Expression
    Each terminal symbol is translated into a corresponding mathml node.*/

    let AMnestingDepth, AMpreviousSymbol, AMcurrentSymbol;

    function AMparseSexpr(str) { //parses str and returns [node,tailstr]
        let symbol, node, result, i, st, // rightvert = false,
            newFrag = document.createDocumentFragment();
        str = AMremoveCharsAndBlanks(str, 0);
        symbol = AMgetSymbol(str); //either a token or a bracket or empty
        if (symbol == null || symbol.ttype == RIGHTBRACKET && AMnestingDepth > 0) {
            return [null, str];
        }
        if (symbol.ttype == DEFINITION) {
            str = symbol.output + AMremoveCharsAndBlanks(str, symbol.input.length);
            symbol = AMgetSymbol(str);
        }
		if(symbol.input == 'sqrt' && str.indexOf('sqrt[') == 0)
		{
			let strOri = str;
			str = AMremoveCharsAndBlanks(str, symbol.input.length);
			result = AMparseSexpr(str);
			let result1str = result[1].toString();
			
			let strWithoutInput = strOri.substring(symbol.input.length);
			
			let offset = strWithoutInput.indexOf(result1str);
            let i;
            let arr;
            let n;
			if(offset > 2)
			{
				i = 0;
				arr = strWithoutInput.split('');
				n = 0;
				for(i in arr)
				{
					if(arr[i] == '[')
					{
						n++;
					}
					if(arr[i] == ']')
					{
						n--;
					}
					if(n == 0)
					{
						i++; //NOSONAR
						break;
					}
				}
				let part0 = strWithoutInput.substring(0, i);
				let nexstr = strWithoutInput.substring(i);
				
				
				i = 0;
				arr = nexstr.split('');
				n = 0;
				for(i in arr)
				{
					if(arr[i] == '{')
					{
						n++;
					}
					if(arr[i] == '}')
					{
						n--;
					}
					if(n == 0)
					{
						i++; //NOSONAR
						break;
					}
				}

				let part1 = nexstr.substring(0, i);
				let part2 = nexstr.substring(i);
				
				let node0 = AMremoveCharsAndBlanks(part0.substring(1, part0.length-2));
				let node1 = AMremoveCharsAndBlanks(part1.substring(1, part1.length-2));
				
				//commented str = AMremoveCharsAndBlanks(strOri, symbol.input.length-1);

                //commented result = AMparseSexpr(str);

                let result1 = AMparseSexpr(node1);
                let result2 = AMparseSexpr(node0);
				
				
				let frag1;
				let frag2;
				let frag3;
				
				let t;
				//commented let node;
				
				t = 'mi';
				if (isIE) 
                {
                    frag1 = document.createElement("m:" + t);
                }
				else frag1 = document.createElementNS(AMmathml, t);
				if (frag1) 
                {
                    frag1.appendChild(result1[0]);
                }
				t = 'mn';
				if (isIE) 
                {
                    frag2 = document.createElement("m:" + t);
                }
				else 
                {
                    frag2 = document.createElementNS(AMmathml, t);
                }
				if (frag2) {
                    frag2.appendChild(result2[0]);
                }
				
				t = 'mroot';
				if (isIE) 
                {
                    frag3 = document.createElement("m:" + t);
                }
				else 
                {
                    frag3 = document.createElementNS(AMmathml, t);
                }
				
				frag3.appendChild(frag1.firstChild);
				frag3.appendChild(frag2.firstChild);
				
                return [frag3, part2];				
				
			}
		}
		
        switch (symbol.ttype) {
            case UNDEROVER:
            case CONST:
                str = AMremoveCharsAndBlanks(str, symbol.input.length);
                return [createMmlNode(symbol.tag, //its a constant
                    document.createTextNode(symbol.output)), str];
            case LEFTBRACKET: //read (expr+)
                AMnestingDepth++;
                str = AMremoveCharsAndBlanks(str, symbol.input.length);
                result = AMparseExpr(str, true);
                AMnestingDepth--;
                if (typeof symbol.invisible == "boolean" && symbol.invisible)
                    node = createMmlNode("mrow", result[0]);
                else {
                    node = createMmlNode("mo", document.createTextNode(symbol.output));
                    node = createMmlNode("mrow", node);
                    node.appendChild(result[0]);
                }
                return [node, result[1]];
            case TEXT:
                if (symbol != AMquote) str = AMremoveCharsAndBlanks(str, symbol.input.length);
                if (str.charAt(0) == "{") i = str.indexOf("}");
                else if (str.charAt(0) == "(") i = str.indexOf(")");
                else if (str.charAt(0) == "[") i = str.indexOf("]");
                else if (symbol == AMquote) i = str.slice(1).indexOf("\"") + 1;
                else i = 0;
                if (i == -1) i = str.length;
                st = str.slice(1, i);
                if (st.charAt(0) == " ") {
                    node = createMmlNode("mspace");
                    node.setAttribute("width", "1ex");
                    newFrag.appendChild(node);
                }
                newFrag.appendChild(
                    createMmlNode(symbol.tag, document.createTextNode(st)));
                if (st.charAt(st.length - 1) == " ") {
                    node = createMmlNode("mspace");
                    node.setAttribute("width", "1ex");
                    newFrag.appendChild(node);
                }
                str = AMremoveCharsAndBlanks(str, i + 1);
                return [createMmlNode("mrow", newFrag), str];
            case UNARYUNDEROVER:
            case UNARY:
                str = AMremoveCharsAndBlanks(str, symbol.input.length);
                result = AMparseSexpr(str);
                if (result[0] == null) return [createMmlNode(symbol.tag,
                    document.createTextNode(symbol.output)), str];
                if (typeof symbol.func == "boolean" && symbol.func) { // functions hack
                    st = str.charAt(0);
                    if (st == "^" || st == "_" || st == "/" || st == "|" || st == "," ||
                        (symbol.input.length == 1 && symbol.input.match(/\w/) && st != "(")) {
                        return [createMmlNode(symbol.tag,
                            document.createTextNode(symbol.output)), str];
                    } else {
                        node = createMmlNode("mrow",
                            createMmlNode(symbol.tag, document.createTextNode(symbol.output)));
                        node.appendChild(result[0]);
                        return [node, result[1]];
                    }
                }
                AMremoveBrackets(result[0]);
                if (symbol.input == "sqrt") { // sqrt
						return [createMmlNode(symbol.tag, result[0]), result[1]];
                } else if (typeof symbol.rewriteleftright != "undefined") { // abs, floor, ceil
                    node = createMmlNode("mrow", createMmlNode("mo", document.createTextNode(symbol.rewriteleftright[0])));
                    node.appendChild(result[0]);
                    node.appendChild(createMmlNode("mo", document.createTextNode(symbol.rewriteleftright[1])));
                    return [node, result[1]];
                } else if (symbol.input == "cancel") { // cancel
                    node = createMmlNode(symbol.tag, result[0]);
                    node.setAttribute("notation", "updiagonalstrike");
                    return [node, result[1]];
                } else if (typeof symbol.acc == "boolean" && symbol.acc) { // accent
                    node = createMmlNode(symbol.tag, result[0]);
                    node.appendChild(createMmlNode("mo", document.createTextNode(symbol.output)));
                    return [node, result[1]];
                } else { // font change command
                    if (!isIE && typeof symbol.codes != "undefined") {
                        for (i = 0; i < result[0].childNodes.length; i++)
                            if (result[0].childNodes[i].nodeName == "mi" || result[0].nodeName == "mi") {
                                st = (result[0].nodeName == "mi" ? result[0].firstChild.nodeValue :
                                    result[0].childNodes[i].firstChild.nodeValue);
                                let newst = [];
                                for (let j = 0; j < st.length; j++)
                                    if (st.charCodeAt(j) > 64 && st.charCodeAt(j) < 91)
                                        newst = newst + symbol.codes[st.charCodeAt(j) - 65];
                                    else if (st.charCodeAt(j) > 96 && st.charCodeAt(j) < 123)
                                    newst = newst + symbol.codes[st.charCodeAt(j) - 71];
                                else newst = newst + st.charAt(j);
                                if (result[0].nodeName == "mi")
                                    result[0] = createMmlNode("mo").
                                appendChild(document.createTextNode(newst));
                                else result[0].replaceChild(createMmlNode("mo").appendChild(document.createTextNode(newst)),
                                    result[0].childNodes[i]);
                            }
                    }
                    node = createMmlNode(symbol.tag, result[0]);
                    node.setAttribute(symbol.atname, symbol.atval);
                    return [node, result[1]];
                }
            case BINARY:
                str = AMremoveCharsAndBlanks(str, symbol.input.length);
                result = AMparseSexpr(str);
                if (result[0] == null) return [createMmlNode("mo",
                    document.createTextNode(symbol.input)), str];
                AMremoveBrackets(result[0]);
                let result2 = AMparseSexpr(result[1]);
                if (result2[0] == null) return [createMmlNode("mo",
                    document.createTextNode(symbol.input)), str];
                AMremoveBrackets(result2[0]);
                if (symbol.input == "color") {
                    if (str.charAt(0) == "{") i = str.indexOf("}");
                    else if (str.charAt(0) == "(") i = str.indexOf(")");
                    else if (str.charAt(0) == "[") i = str.indexOf("]");
                    st = str.slice(1, i);
                    node = createMmlNode(symbol.tag, result2[0]);
                    node.setAttribute("mathcolor", st);
                    return [node, result2[1]];
                }
                if (symbol.input == "root" || symbol.output == "stackrel")
                    newFrag.appendChild(result2[0]);
                newFrag.appendChild(result[0]);
                if (symbol.input == "frac") newFrag.appendChild(result2[0]);
                return [createMmlNode(symbol.tag, newFrag), result2[1]];
            case INFIX:
                str = AMremoveCharsAndBlanks(str, symbol.input.length);
                return [createMmlNode("mo", document.createTextNode(symbol.output)), str];
            case SPACE:
                str = AMremoveCharsAndBlanks(str, symbol.input.length);
                node = createMmlNode("mspace");
                node.setAttribute("width", "1ex");
                newFrag.appendChild(node);
                newFrag.appendChild(
                    createMmlNode(symbol.tag, document.createTextNode(symbol.output)));
                node = createMmlNode("mspace");
                node.setAttribute("width", "1ex");
                newFrag.appendChild(node);
                return [createMmlNode("mrow", newFrag), str];
            case LEFTRIGHT:
                //commented if (rightvert) return [null,str]; else rightvert = true;
                AMnestingDepth++;
                str = AMremoveCharsAndBlanks(str, symbol.input.length);
                result = AMparseExpr(str, false);
                AMnestingDepth--;
                st = "";
                if (result[0].lastChild != null)
                    st = result[0].lastChild.firstChild.nodeValue;
                if (st == "|") { // its an absolute value subterm
                    node = createMmlNode("mo", document.createTextNode(symbol.output));
                    node = createMmlNode("mrow", node);
                    node.appendChild(result[0]);
                    return [node, result[1]];
                } else { // the "|" is a \mid so use unicode 2223 (divides) for spacing
                    node = createMmlNode("mo", document.createTextNode("\u2223"));
                    node = createMmlNode("mrow", node);
                    return [node, str];
                }
            default: //NOSONAR
                str = AMremoveCharsAndBlanks(str, symbol.input.length);
                return [createMmlNode(symbol.tag, //its a constant
                    document.createTextNode(symbol.output)), str];
        }
    }

    function AMparseIexpr(str) {
        let symbol, sym1, sym2, node, result, underover;
        str = AMremoveCharsAndBlanks(str, 0);
        sym1 = AMgetSymbol(str);
        result = AMparseSexpr(str);
        node = result[0];
        str = result[1];
        symbol = AMgetSymbol(str);
        if (symbol.ttype == INFIX && symbol.input != "/") {
            str = AMremoveCharsAndBlanks(str, symbol.input.length);
            //commented if (symbol.input == "/") result = AMparseIexpr(str); else ...
            result = AMparseSexpr(str);
            if (result[0] == null) // show box in place of missing argument
                result[0] = createMmlNode("mo", document.createTextNode("\u25A1"));
            else AMremoveBrackets(result[0]);
            str = result[1];
            //commented if (symbol.input == "/") AMremoveBrackets(node);
            underover = (sym1.ttype == UNDEROVER || sym1.ttype == UNARYUNDEROVER);
            if (symbol.input == "_") {
                sym2 = AMgetSymbol(str);
                if (sym2.input == "^") {
                    str = AMremoveCharsAndBlanks(str, sym2.input.length);
                    let res2 = AMparseSexpr(str);
                    AMremoveBrackets(res2[0]);
                    str = res2[1];
                    node = createMmlNode((underover ? "munderover" : "msubsup"), node);
                    node.appendChild(result[0]);
                    node.appendChild(res2[0]);
                    node = createMmlNode("mrow", node); // so sum does not stretch
                } else {
                    node = createMmlNode((underover ? "munder" : "msub"), node);
                    node.appendChild(result[0]);
                }
            } else if (symbol.input == "^" && underover) {
                node = createMmlNode("mover", node);
                node.appendChild(result[0]);
            } else {
                node = createMmlNode(symbol.tag, node);
                node.appendChild(result[0]);
            }
            if (typeof sym1.func != 'undefined' && sym1.func) {
                sym2 = AMgetSymbol(str);
                if (sym2.ttype != INFIX && sym2.ttype != RIGHTBRACKET) {
                    result = AMparseIexpr(str);
                    node = createMmlNode("mrow", node);
                    node.appendChild(result[0]);
                    str = result[1];
                }
            }
        }
        return [node, str];
    }

    function AMparseExpr(str, rightbracket) {
        let symbol, node, result, i,
            newFrag = document.createDocumentFragment();
        do {
            str = AMremoveCharsAndBlanks(str, 0);
            result = AMparseIexpr(str);
            node = result[0];
            str = result[1];
            symbol = AMgetSymbol(str);
            if (symbol.ttype == INFIX && symbol.input == "/") {
                str = AMremoveCharsAndBlanks(str, symbol.input.length);
                result = AMparseIexpr(str);
                if (result[0] == null) // show box in place of missing argument
                    result[0] = createMmlNode("mo", document.createTextNode("\u25A1"));
                else AMremoveBrackets(result[0]);
                str = result[1];
                AMremoveBrackets(node);
                node = createMmlNode(symbol.tag, node);

                node.appendChild(result[0]);
                newFrag.appendChild(node);
                symbol = AMgetSymbol(str);
            } else if (node != undefined) newFrag.appendChild(node);
        } 
        while ((symbol.ttype != RIGHTBRACKET && (symbol.ttype != LEFTRIGHT || rightbracket) || AMnestingDepth == 0) && symbol != null && symbol.output != ""); //NOSONAR
        if (symbol.ttype == RIGHTBRACKET || symbol.ttype == LEFTRIGHT) {
            //commented if (AMnestingDepth > 0) AMnestingDepth--;
            let len = newFrag.childNodes.length;
            if (len > 0 && newFrag.childNodes[len - 1].nodeName == "mrow" &&
                newFrag.childNodes[len - 1].lastChild &&
                newFrag.childNodes[len - 1].lastChild.firstChild) { //matrix
                //removed to allow row vectors: //&& len>1 && 
                //newFrag.childNodes[len-2].nodeName == "mo" &&
                //newFrag.childNodes[len-2].firstChild.nodeValue == ","
                let right = newFrag.childNodes[len - 1].lastChild.firstChild.nodeValue;
                if (right == ")" || right == "]") 
                {
                    let left = newFrag.childNodes[len - 1].firstChild.firstChild.nodeValue;
                    if (left == "(" && right == ")" && symbol.output != "}" ||
                        left == "[" && right == "]") 
                        {
                        let pos = []; // positions of commas
                        let matrix = true;
                        let m = newFrag.childNodes.length;
                        for (i = 0; matrix && i < m; i = i + 2) {
                            pos[i] = [];
                            node = newFrag.childNodes[i];
                            if (matrix)
                            {
                                matrix = node.nodeName == "mrow" &&
                                (i == m - 1 || node.nextSibling.nodeName == "mo" &&
                                    node.nextSibling.firstChild.nodeValue == ",") &&
                                node.firstChild.firstChild.nodeValue == left &&
                                node.lastChild.firstChild.nodeValue == right;
                            }
                            if (matrix)
                            {
                                for (let j = 0; j < node.childNodes.length; j++)
                                {
                                    if (node.childNodes[j].firstChild.nodeValue == ",")
                                    {
                                        pos[i][pos[i].length] = j;
                                    }
                                }
                            }
                            if (matrix && i > 1) 
                            {
                                matrix = pos[i].length == pos[i - 2].length;
                            }
                        }
                        matrix = matrix && (pos.length > 1 || pos[0].length > 0);
                        if (matrix) {
                            let row;
                            let frag;
                            //commented let n;
                            let k;
                            let table = document.createDocumentFragment();
                            for (i = 0; i < m; i = i + 2) {
                                row = document.createDocumentFragment();
                                frag = document.createDocumentFragment();
                                node = newFrag.firstChild; // <mrow>(-,-,...,-,-)</mrow>
                                let n = node.childNodes.length;
                                let j;
                                k = 0;
                                node.removeChild(node.firstChild); //remove (
                                //commented let maxN = node.childNodes.length - 1;
                                for (j = 1; j < n - 1; j++) 
                                {
                                    if (typeof pos[i][k] != "undefined" && j == pos[i][k]) {
                                        node.removeChild(node.firstChild); //remove ,
                                        row.appendChild(createMmlNode("mtd", frag));
                                        k++;
                                    } 
                                    else 
                                    {
                                        frag.appendChild(node.firstChild);
                                    }
                                }
                                row.appendChild(createMmlNode("mtd", frag));
                                if (newFrag.childNodes.length > 2) {
                                    newFrag.removeChild(newFrag.firstChild); //remove <mrow>)</mrow>
                                    newFrag.removeChild(newFrag.firstChild); //remove <mo>,</mo>
                                }
                                table.appendChild(createMmlNode("mtr", row));
                            }
                            node = createMmlNode("mtable", table);
                            if (typeof symbol.invisible == "boolean" && symbol.invisible) 
                            {
                                node.setAttribute("columnalign", "left");
                            }
                            newFrag.replaceChild(node, newFrag.firstChild);
                        }
                    }
                }
            }
            str = AMremoveCharsAndBlanks(str, symbol.input.length);
            if (typeof symbol.invisible != "boolean" || !symbol.invisible) {
                node = createMmlNode("mo", document.createTextNode(symbol.output));
                newFrag.appendChild(node);
            }
        }
        return [newFrag, str];
    }

    function parseMath(str, latex, repair) {
        let frag, node;
        AMnestingDepth = 0;
		
		if(repair)
		{
			str = reconstructMatrix(str);
		}
		
		
        //some basic cleanup for dealing with stuff editors like TinyMCE adds
		str = str || '';
		str = str.toString();
        str = str.replace(/&nbsp;/g, "");
        str = str.replace(/&gt;/g, ">");
        str = str.replace(/&lt;/g, "<");
        str = str.replace(/&amp;/g, "&");
        str = str.replace(/(Sin|Cos|Tan|Arcsin|Arccos|Arctan|Sinh|Cosh|Tanh|Cot|Sec|Csc|Log|Ln|Abs)/g, function(v) {
            return v.toLowerCase();
        });
		
		str = str.split('\\left(').join('(');
		str = str.split('\\right)').join(')');

		str = str.split('\\left\\{').join('{');
		str = str.split('\\right\\}').join('}');

		str = str.split('\\left[').join('[');
		str = str.split('\\right]').join(']');
		str = str.split('\\left|').join('|');
		str = str.split('\\right|').join('|');
		
		str = str.split('\\sqrt[').join('\\root[');
		
		str = str.split('&amp;').join('&');
		
		str = str.split('\\begin{matrix}').join('\\begin{array}');
		str = str.split('\\end{matrix}').join('\\end{array}');
		str = str.split('\\begin{array}{ccc}').join('\\begin{array}');
		if(str.indexOf('\\begin{array}') == 0)
		{
			str = '['+str+']';
		}
		
		
		// process matrix
		//commented let len = str.length;
		let found;
		let p1 = 0;
        let p2 = 0;
        //commented let tmp1 = '';
        //commented let tmp2 = '';
		let arr1 = [], arr2 = [], array3 = [], startPos = [], endPos = [], symbolBefofe = [], symbolAfter = [];
		do{
			found = false;
			p1 = str.indexOf('\\begin{array}', p2);
			if(p1 > -1)
			{
				p2 = str.indexOf('\\end{array}', p1+12);
				if(p2 > -1)
				{
					arr1.push(str.substring(p1, p2+11-p1));
					arr2.push(str.substring(p1+13, p2-p1-13));
					let sp, ep;
					sp = p1;
					ep = p2+11;
					startPos.push(sp);
					endPos.push(ep);
					
					// cari sebelum
					if(sp == 0)
					{
						
						symbolBefofe.push('');
						symbolAfter.push('');
					}
					else
					{
						if(str.substring(sp-1,1) == '|' && str.substring(ep, 1) == '|')
						{
							symbolBefofe.push('|');
							symbolAfter.push('|');
						}
						else if(str.substring(sp-1,1) == '[' && str.substring(ep, 1) == ']')
						{
							symbolBefofe.push('[');
							symbolAfter.push(']');
						}
						else
						{
							symbolBefofe.push('');
							symbolAfter.push('');
						}
					}
					
					found = true;
				}
				
			}
		}
		while(found);
		
		let i;
        let j;
        let k;
        let l;
        let m;
        let n;
        //commented let o;
        let p;
        //commented let q;
        let r;
        //commented let s;
		if(arr2.length > 0)
		{
			for(i in arr2)
			{ 
				j = arr2[i] || '';
				j = j.toString();
				k = j.trim();
				l = k.split('\\\\');
				r = new Array();
				for(m = 0; m < l.length; m++)
				{
					n = l[m] || '';
					n = n.toString();
					n = n.trim();
					if(n.length)
					{
						p = n.split('&'); // array kolom
						r.push(p); // array baris
					}
				}
				//
				let objectArr = {
				'matrix' : r,
				'startPos' : startPos[i],
				'endPos' : endPos[i],
				'symbolBefore' : symbolBefofe[i],
				'symbolAfter' : symbolAfter[i]
				}
				
				
				array3.push(objectArr); // matrix
			}
			if(array3.length > 0)
			{
				for(i = 0; i< array3.length; i++)
				{
					let a = [];
                    //commented let b;
                    //commented let c;
					if(array3[i].symbolBefore == '|')
					{
						let k;
                        let l;
                        //commented let m;
						for(k = 0; k < array3[i].matrix.length; k++)
						{
							l = array3[i].matrix[k];
							a.push('['+l.join(', ')+']');
						}
						str = str.replace('|'+arr1[i]+'|', '|'+a.join(', ')+'|'); 
					}
					else if(array3[i].symbolBefore == '[')
					{
						let k;
                        let l;
                        //commented let m;
						for(k = 0; k < array3[i].matrix.length; k++)
						{
							l = array3[i].matrix[k];
							a.push('('+l.join(', ')+')');
						}
						str = str.replace('['+arr1[i]+']', '['+a.join(', ')+']'); 
					}
					else if(array3[i].symbolBefore == '')
					{
						let k, l, m;
						for(k = 0; k < array3[i].matrix.length; k++)
						{
							l = array3[i].matrix[k];
							a.push('('+l.join('), (')+')');
						}
						str = str.replace('['+arr1[i]+']', '['+a.join(', ')+']'); 
					}
				}
			}
		}
		
        frag = AMparseExpr(str.replace(/^\s+/g, ""), false)[0];
        node = createMmlNode("mstyle", frag);
        if (mathcolor != "") 
		{
			node.setAttribute("mathcolor", mathcolor);
		}
        if (mathfontfamily != "")
		{
			node.setAttribute("mathvariant", mathfontfamily);
		}
        if (displaystyle) 
		{
			node.setAttribute("displaystyle", "true");
		}
        node = createMmlNode("math", node);
        if (showasciiformulaonhover) //fixed by djhsu so newline
		{
            // NOSONAR
            /**
             * Commented
             * node.setAttribute("title", str.replace(/\s+/g, " ")); //does not show in Gecko
            **/
		}
        return node;
    }

    function strarr2docFrag(arr, linebreaks, latex) //NOSONAR
    {
        let newFrag = document.createDocumentFragment();
        let expr = false;
        for (let i in arr) 
		{
            if (expr) 
			{
				newFrag.appendChild(parseMath(arr[i], latex));
			}
            else 
			{
                let arri = (linebreaks ? arr[i].split("\n\n") : [arr[i]]);
                newFrag.appendChild(createElementXHTML("span").appendChild(document.createTextNode(arri[0])));
                for (let j = 1; j < arri.length; j++) {
                    newFrag.appendChild(createElementXHTML("p"));
                    newFrag.appendChild(createElementXHTML("span").appendChild(document.createTextNode(arri[j])));
                }
            }
            expr = !expr;
        }
        return newFrag;
    }

    function AMautomathrec(str) //NOSONAR
    {
        //formula is a space (or start of str) followed by a maximal sequence of *two* or more tokens, possibly separated by runs of digits and/or space.
        //tokens are single letters (except a, A, I) and ASCIIMathML tokens
        let texcommand = "\\\\[a-zA-Z]+|\\\\\\s|";
        let ambigAMtoken = "\\b(?:oo|lim|ln|iiiint|oiiint|iiint|oiint|iint|oint|int|del|grad|aleph|prod|prop|sinh|cosh|tanh|cos|sec|pi|tt|fr|sf|sube|supe|sub|sup|det|mod|gcd|lcm|min|max|vec|ddot|ul|chi|eta|nu|mu)(?![a-z])|";
        let englishAMtoken = "\\b(?:sum|ox|log|sin|tan|dim|hat|bar|dot)(?![a-z])|";
        let secondenglishAMtoken = "|\\bI\\b|\\bin\\b|\\btext\\b"; // took if and or not out
        let simpleAMtoken = "NN|ZZ|QQ|RR|CC|TT|AA|EE|sqrt|dx|dy|dz|dt|xx|vv|uu|nn|bb|cc|csc|cot|alpha|beta|delta|Delta|epsilon|gamma|Gamma|kappa|lambda|Lambda|omega|phi|Phi|Pi|psi|Psi|rho|sigma|Sigma|tau|theta|Theta|xi|Xi|zeta"; // uuu nnn?
        let letter = "[a-zA-HJ-Z](?=(?:[^a-zA-Z]|$|" + ambigAMtoken + englishAMtoken + simpleAMtoken + "))|";
        let token = letter + texcommand + "\\d+|[-()[\\]{}+=*&^_%\\\@/<>,\\|!:;'~]|\\.(?!(?:\x20|$))|" + ambigAMtoken + englishAMtoken + simpleAMtoken;
        let re = new RegExp("(^|\\s)(((" + token + ")\\s?)((" + token + secondenglishAMtoken + ")\\s?)+)([,.?]?(?=\\s|$))", "g");
        str = str.replace(re, " `$2`$7");
        let arr = str.split(AMdelimiter1);
        let re1 = new RegExp("(^|\\s)([b-zB-HJ-Z+*<>]|" + texcommand + ambigAMtoken + simpleAMtoken + ")(\\s|\\n|$)", "g");
        let re2 = new RegExp("(^|\\s)([a-z]|" + texcommand + ambigAMtoken + simpleAMtoken + ")([,.])", "g"); // removed |\d+ for now
        let i;
        for (i = 0; i < arr.length; i++) //single nonenglish tokens
        {
            if (i % 2 == 0) {
                arr[i] = arr[i].replace(re1, " `$2`$3");
                arr[i] = arr[i].replace(re2, " `$2`$3");
                arr[i] = arr[i].replace(/([{}[\]])/, "`$1`");
            }
        }
        str = arr.join(AMdelimiter1);
        str = str.replace(/((^|\s)\([a-zA-Z]{2,}.*?)\)`/g, "$1`)"); //fix parentheses
        str = str.replace(/`(\((a\s|in\s))(.*?[a-zA-Z]{2,}\))/g, "$1`$3"); //fix parentheses
        str = str.replace(/\sin`/g, "` in");
        str = str.replace(/`(\(\w\)[,.]?(\s|\n|$))/g, "$1`");
        str = str.replace(/`([0-9.]+|e.g|i.e)`(\.?)/gi, "$1$2");
        str = str.replace(/`([0-9.]+:)`/g, "$1");
        return str;
    }
	function latexToMathML(latexString, latex, repair)
	{
		let elem = parseMath(latexString, latex, repair);
		elem.setAttribute('xmlns', AMmathml);
		return elem.outerHTML;
	}
	function latexToSVG(latexString, latex, repair)
	{
		let elem = parseMath(latexString, latex, repair);
		elem.setAttribute('xmlns', AMmathml);
		let span = document.createElement('span');
		span.setAttribute('id', 'mathml');
		span.style.fontFamily = 'Times';
		span.style.fontSize = '16px';
		span.style.visibility = 'hidden';
		span.style.visibility = 'hidden';
		span.style.display = 'inline-block';
		span.style.position = 'absolute';
		span.style.left = '-100000px';
		span.style.top = '-100000px';
		span.appendChild(elem);
		document.body.appendChild(span);
		let e = elem.getBoundingClientRect();
		let s = span.getBoundingClientRect();
		document.body.removeChild(span);
		let width = Math.ceil(e.width);
		let height = Math.ceil(s.height);
		
		let svg = '<svg xmlns="http://www.w3.org/2000/svg" width="'+width+'" height="'+height+'"><foreignObject width="100%" height="100%"><div xmlns="http://www.w3.org/1999/xhtml">'+elem.outerHTML+'</div></foreignObject></svg>';
		return svg;
	}


	let map = {9618:32, 8289:32, 12310:40, 12311:41};
	let sym = {
		'∑':'sum', 
		'√':'sqrt',
		'⨌':'iiiint',
		'∰':'oiiint',
		'∭':'iiint',
		'∯':'oiint',
		'∬':'iint',
		'∮':'oint',
		'∫':'int',
		'┬':'_',
		
		'¹':'^{1}',
		'²':'^{2}',
		'³':'^{3}',
		'½':'frac{1}{2}',
		'⅓':'frac{1}{3}',
		'¼':'frac{1}{4}',
		'⅕':'frac{1}{5}',
		'⅙':'frac{1}{6}',
		'⅛':'frac{1}{8}',
		
		'⅔':'frac{2}{3}',
		'⅖':'frac{2}{5}',
	
		'¾':'frac{3}{4}',
		'⅗':'frac{3}{5}',
		'⅜':'frac{3}{8}',
		
		'⅘':'frac{4}{5}',
		
		'⅚':'frac{5}{6}',
		'⅝':'frac{5}{8}',
		
		'⅞':'frac{7}{8}',
        '⅐':'frac{1}{7}',
        '⅑':'frac{1}{9}',
        '⅒':'frac{1}{10}'
		};
	let token = 
	"iiiint|oiiint|iiint|oiint|iint|oint|int|lim|ln|del|grad|aleph|prod|prop|sinh|cosh|tanh|cos|sec|pi|tt|fr|sf|sube|supe|sub|sup|det|mod|gcd|lcm|min|max|vec|ddot|ul|chi|eta|nu|mu|sum|ox|log|sin|tan|dim|hat|bar|dot";
	
			
	function filterData(data)
	{
		let i, j, k, l;
		
		
		for(i in map)
		{
			j = map[i];
			k = String.fromCharCode(i);
			l = String.fromCharCode(j);
			data = data.split(k).join(l);
		}
		let arr = token.split("|");
		for(i in arr)
		{
			data = data.split(arr[i]).join("\\"+arr[i]);
			data = data.split('\\\\'+arr[i]).join("\\"+arr[i]);
		}
		for(i in sym)
		{
			data = data.split(i).join("\\"+sym[i]);
		}
		data = data.split('\\^').join('^');
		data = data.split('\\_').join('_');
		// repair integral
		// iiiint
		data = data.split('\\i\\i\\i\\int').join('\\iiiint');

		// oiiint
		data = data.split('\\o\\i\\i\\int').join('\\oiiint');
		// iiint
		data = data.split('\\i\\i\\int').join('\\iiint');
	
		// oiint
		data = data.split('\\o\\i\\int').join('\\oiint');
		// iint
		data = data.split('\\i\\i\\int').join('\\iint');
	
		// oint
		data = data.split('\\o\\int').join('\\oint');
		
		data = data.split('\\\\iiiint').join('\\iiiint');
		data = data.split('\\\\oiiint').join('\\oiiint');
		data = data.split('\\\\iiint').join('\\iiint');
		data = data.split('\\\\oiint').join('\\oiint');
		data = data.split('\\\\iint').join('\\iint');
		data = data.split('\\\\oint').join('\\oint');
		data = data.split('\\\\i\\int').join('\\iint');
		data = data.split('\\i\\int').join('\\iint');
		data = data.split('\\\\int').join('\\int');

        // circ
		data = data.split('°').join('^{\\circ}'); 
		data = data.split('&radic;').join('\\sqrt');

		return data;
	}
	function reconstructVector(input)
	{
		let str1 = input;
		let str2 = '';
		//commented let i;
        let j;
        //commented let k;
		//commented let t1;
        let t2;
        let t3;
        let t4;
        //commented let final;
		let flagCount = 0;
		
		if(str1.indexOf(' ⃗') != -1)
		{
			let pos1;
            let pos2 = 0;
            //commented let pos3;
            //commented let pos4;
			do{
				pos1 = str1.indexOf(' ⃗', pos2);
				if(pos1 != -1)
				{
					str2 = str1.substring(0, pos1);
					pos2 = pos1 + 1;
					if(str1.substring(pos1-1, 1) == ')')
					{
						flagCount = 0;
						for(j = str2.length - 1; j >= 0; j--)
						{
							if(str2.substring(j, 1) == ')')
							{
								flagCount++;
							}
							if(str2.substring(j, 1) == '(')
							{
								flagCount--;
							}
							if(flagCount == 0)
							{
								break;
							}
						}
						t2 = str2.substring(j+1, str2.length - j - 2);
						t3 = str2.substring(j, str2.length - j);
						t4 = '\\vec{'+t2+'} ';
						str1 = str1.replace(t3+' ⃗', t4);
					}
					else
					{
						t3 = str2.substring(j-1, 1);
						t4 = '\\vec{'+t3+'} ';
						str1 = str1.replace(t3+' ⃗', t4);
					}
				}
			}
			while(pos1 != -1);
		}
		return str1;
	}

	function reconstructMatrix(data)
	{
		let i;
        //commented let j;
        //commented let k;
        //commented let l;
        //commented let m;
        //commented let chk;
		let arr = data.split('');
		let arr1 = [];
        //commented let arr2 = [];
        //commented let array3 = [];
		let counting = false;
		let cnt = 0;
		let startPos = -1;
		let endPos = -1;
		let len = arr.length;
		let h = 0;
		for(i in arr)
		{
			if(i < len-2)
			{
				if(arr[i].charCodeAt(0) == 9632)
				{
					counting = true;
					startPos = i;
					continue;
					// start
				}
			}
			if(counting)
			{
				if(arr[i] == '(')
				{
					cnt++;
				}
				if(arr[i] == ')')
				{
					cnt--;
				}
				if(cnt == 0)
				{
					endPos = i;
					let txt = data.substring(startPos, endPos-startPos+2);
					if(startPos > 0)
					{
						if(arr[startPos-1] == '[' || arr[startPos-1] == '|') 
						{
							txt = data.substring(startPos-1, endPos-startPos+3);
						}
					}
					arr1.push(txt);
					counting = false;
				}
			}
			h++;
		}
		for(i in arr1)
		{
			let t1 = arr1[i] || '';
			t1 = t1.toString();
			let t2 = t1.replace(String.fromCharCode(9632), '');
			
			let t3 = t2.substring(0, t2.length);
			let t4 = t3.split('@').join('),(');
			let t5 = t4.split('&').join(',');
			let t6 = '['+t5+']';
			if((t6.substring(0, 2) == '[[' && t6.substring(t6.length-2,2) == ']]')
            ||
            (t6.substring(0, 2) == '[|' && t6.substring(t6.length-2,2) == '|]')
            )
			{
				t6 = t6.substring(1, t6.length - 2);
			}
	
			let test = t6.replace(/[^\(^\)^\[^\]]+/gi, ' ');
			test = test.trim();
			let arr_test = test.split(' ');
			if(arr_test[0] == '[(' && arr_test[arr_test.length - 1] == '))]')
			{
				let test2 = t6.substring(2, t6.length - 5);
				t6 = '('+test2+'))';
			}

			data = data.replace(t1, t6);
		}
		return data;
	}
	function reconstructSqrtWord(data)
	{
		let i;
        //commented let j;
        //commented let k;
        //commented let l;
        //commented let m;
        //commented let chk;
		let arr = data.split('');
		let arr1 = [];
        //commented let arr2 = [];
        //commented let array3 = [];
		let counting = false;
		let cnt = 0;
		let startPos = -1;
		let endPos = -1;
		let len = arr.length;
		let h = 0;
		for(i in arr)
		{
			if(i < len-2)
			{
				if(arr[i].charCodeAt(0) == 8730 && (arr[i].charCodeAt(1) == '('))
				{
					counting = true;
					startPos = i;
					continue;
					// start
				}
			}
			if(counting)
			{
				if(arr[i] == '(')
				{
					cnt++;
				}
				if(arr[i] == ')')
				{
					cnt--;
				}
				if(cnt == 0)
				{
					endPos = i;
					let txt = data.substring(startPos, endPos-startPos+1);
					if(startPos > 0)
					{
						txt = data.substring(startPos-1, endPos-startPos+2);
					}
					arr1.push(txt);
					counting = false;
				}
			}
			h++;
		}
		for(i in arr1)
		{
			let t1 = arr1[i] || '';
			t1 = t1.toString();
            let t2;
			if(t1.indexOf('}{') > 0 || t1.indexOf('&') > 0)
			{
				t2 = t1.replace(String.fromCharCode(8730)+'(', '\\root{');
			}
			else
			{
				t2 = t1.replace(String.fromCharCode(8730)+'(', '\\sqrt{');
			}
			let t3 = t2.substring(0, t2.length-1);
			let t5 = t3.split('&').join('}{');

			let t6 = t5+'}';

			data = data.replace(t1, t6);
		}
		return data;
	}
	

	init();

    //expose some functions to outside
	asciimath.filterData = filterData;
	asciimath.reconstructMatrix = reconstructMatrix;
	asciimath.reconstructVector = reconstructVector;
	asciimath.reconstructSqrtWord = reconstructSqrtWord;
	asciimath.newcommand = newcommand;
	asciimath.newsymbol = newsymbol;
	asciimath.parseMath = parseMath;
	asciimath.latexToSVG = latexToSVG;
	asciimath.latexToMathML = latexToMathML;
})();
