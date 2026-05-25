function generateResponse(origin, destination){

    var response = document.getElementById("insights"); 

    fetch("../gemini_insights/gemini_api.php", {
        method : "POST",
        body: JSON.stringify({
            prompt : "Provide essential travel destination insights for a traveler from " + origin + " going to " + destination + ". Focus on entry requirements (visa, passport, etc), local language and culture highlights, any food based requirements and critical health or safety advisories. Summarise this into a paragraph of no more than 5 sentences."

        })
    }
    ).then((res) => res.text())
    .then((res) => {
        response.innerHTML = res;
    });

}