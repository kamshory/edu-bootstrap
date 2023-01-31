function createLineObject(lineNumber, lineContent)
{
	let nPipe = lineContent.split('|').length - 1;
	let x = lineContent.replace(/[^-\|]/gi, '');
	let x2 = lineContent.replace(/\s/g,'');
	let hasPipeAndDash = x == x2 && x2.length > 1;
	return {lineNumber: parseInt(lineNumber), content:lineContent, pipe: nPipe, pipeDash: hasPipeAndDash, startTable:false, inTable:false, endTable:false};
}

function detectTable(html) //NOSONAR
{
	let html2 = html;
    html = html.split('\r\n').join('\n');
    html2 = html2.split('\r\n').join('\n');
	let arr = html.split("\n");
	let arr2 = html2.split("\n");
	let lineObj = [];
	for(let i in arr)
	{
		arr2[i] = arr[i].trim();
		lineObj.push(createLineObject(i, arr2[i]));
	}
	let inTable = false;
	let tableObj = [];
	for(let i = 1, j = 0; i<lineObj.length; i++)
	{
		if(lineObj[i].pipeDash && lineObj[i-1].pipe > 0)
		{
			inTable = true;
			lineObj[i].inTable = true;
			lineObj[i-1].startTable = true;
			tableObj[j] = [];
			tableObj[j].push(lineObj[i-1]); 
			tableObj[j].push(lineObj[i]); 
		}
		if(inTable && !lineObj[i].pipeDash && lineObj[i].pipe > 0)
		{
			lineObj[i].inTable = true;
            if(i == lineObj.length - 1)
            {
                lineObj[i].endTable = true;
            }
			tableObj[j].push(lineObj[i]);
		}
		if(inTable && lineObj[i].pipe == 0)
		{
			inTable = false;
			lineObj[i-1].endTable = true;
			j++;
		}
	}

	for(let i in arr)
	{
		arr[i] = arr[i] + "<br />";
	}

	for(let i in tableObj)
	{
		let tab = tableObj[i]
		for(let i in tab)
		{
			let content = '';
			if(tab[i].startTable)
			{
				content = createTableHeader(tab[i].content);
			}
			else if(tab[i].inTable)
			{
				if(tab[i].pipeDash)
				{
					// Do nothing
				}
				else if(tab[i].endTable)
				{
					content = createTableContent(tab[i].content)+'</tbody></table>';
				}
				else 
				{
					content = createTableContent(tab[i].content);
				}
			}
			arr[tab[i].lineNumber] = content;
		}
	}
	return arr.join('\r\n');
}

function createTableHeader(input)
{
	input = input.trim();
	let arr = input.split('|');
	let content = '<table class="table table-bordered"><thead><tr>';
	for(let i = 0; i < arr.length; i++)
	{
		if((i == 0 && arr[i] != '') || (i == arr.length -1 && arr[i] != '') || arr[i] != '')
		{
			content += '<td>'+arr[i]+'</td>';
		}
	}
	content += '</tr></thead><tbody>';
	return content;
}
function createTableContent(input)
{
	input = input.trim();
	let arr = input.split('|');
	let content = '<tr>';
	for(let i = 0; i < arr.length; i++)
	{
		if((i == 0 && arr[i] != '') || (i == arr.length -1 && arr[i] != '') || arr[i] != '')
		{
			// start with |
			content += '<td>'+arr[i]+'</td>';
		}
	}
	content += '</tr>';
	return content;
}