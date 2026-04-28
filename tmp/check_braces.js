const fs=require('fs');
const s=fs.readFileSync('c:/Users/Gershan/RetailPos/frontend/src/App.js','utf8');
let stack=[];
let inSingle=false,inDouble=false,inBack=false,inBlock=false,inLine=false;
let line=1,col=0;
for(let i=0;i<s.length;i++){
  const ch=s[i];
  col++;
  if(ch==='\n'){
    line++;
    col=0;
    inLine=false;
    continue;
  }
  if(inLine){
    continue;
  }
  if(inBlock){
    if(ch==='*' && s[i+1]=='/'){
      inBlock=false; i++; col++; continue;
    } else continue;
  }
  if(!inSingle && !inDouble && !inBack){
    if(ch==='/' && s[i+1]==='/'){ inLine=true; i++; col++; continue; }
    if(ch==='/' && s[i+1]==='*'){ inBlock=true; i++; col++; continue; }
  }
  if(!inDouble && !inBack && ch==="'") { inSingle = !inSingle; continue; }
  if(!inSingle && !inBack && ch==='"'){ inDouble = !inDouble; continue; }
  if(!inSingle && !inDouble && ch==='`'){ inBack = !inBack; continue; }
  if(inSingle||inDouble||inBack) continue;
  if(ch==='{'){ stack.push({line,col,i}); }
  else if(ch==='}'){
    if(stack.length) stack.pop(); else console.log('Unmatched closing } at',line,col);
  }
}
if(stack.length) console.log('Unmatched opens (first):',stack[0]); else console.log('All braces matched');
