function checkStop(){
    var from= document.getElementById("from").value;
    var to= document.getElementById("to").value;
    var seats= parseInt(document.getElementById("seats").value);
    
    if(from>=to || seats<=0 || from== 'From:' || to== 'To:'){
        document.cookie= "status=false";
        alert("Wrong insertion");
    }
    else{
        document.cookie= "status=true";
	}
}

function setStationSrc(){
	var dep= document.getElementById("listDep").value;
	var depInput= document.getElementById("from").value;
	
	if(dep!='' && dep!= depInput){
		document.getElementById("from").value= dep;
		return;
	}
	else{
		document.getElementById("from").value= "From:";
		return;
	}
}

function setStationDest(){
	var dest= document.getElementById("listDest").value;
	var destInput= document.getElementById("to").value;
	
	if(dest!='' && dest!= destInput){
		document.getElementById("to").value= dest;
		return;
	}
	else{
		document.getElementById("to").value= "To:";
		return;
	}
}

function setListSrc(){
	 var x = document.getElementById("listDep");
	 var src= document.getElementById("from").value;
	 var i, found=-1;
	 
	 src= src.toUpperCase();
	 for(i=0; i<x.options.length && found==-1; i++){
		 if(src == x.options[i].text)
			 found= i;
	 }
	 if(found!=-1)
		 x[found].selected= true;
	 else
		 x[x.options.length-1].selected= true;		 
}

function setListDest(){
	 var x = document.getElementById("listDest");
	 var dest= document.getElementById("to").value;
	 var i, found=-1;
	 
	 dest= dest.toUpperCase();
	 for(i=0; i<x.options.length && found==-1; i++)
		 if(dest == x.options[i].text)
			 found= i;
	 if(found!=-1)
		 x[found].selected= true;
	 else
		 x[x.options.length-1].selected= true;		 
}