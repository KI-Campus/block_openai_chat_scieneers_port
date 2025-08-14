var questionString = 'Ask a question...'
var errorString = 'An error occurred! Please try again later.'

const init = (Y, sourceOfTruth, infoSource, id) => {
    document.querySelector('#openai_input-'+id).addEventListener('keyup', e => {
        if (e.which === 13 && e.target.value !== "") {
            addToChatLog('user', e.target.value, id)
            createCompletion(e.target.value, sourceOfTruth, infoSource, id, kiCampusChatBotCourseID)
            e.target.value = ''
        }
    })

    require(['core/str'], function(str) {
        var strings = [
            {
                key: 'askaquestion',
                component: 'block_openai_chat_scieneers'
            },
            {
                key: 'erroroccurred',
                component: 'block_openai_chat_scieneers'
            },
        ];
        str.get_strings(strings).then((results) => {
            questionString = results[0];
            errorString = results[1];
        });
    });
}

const addToChatLog = (type, message, id) => {
    /**
     * Add a message to the chat UI
     * @param {string} type Which side of the UI the message should be on. Can be "user" or "bot"
     * @param {string} message The text of the message to add
     */
    let messageContainer = document.querySelector('#openai_chat_scieneers_log-'+id)
    messageContainer.insertAdjacentHTML('beforeend', `
        <div class='openai_message ${type}'>
            ${message}
        </div>
    `)
    messageContainer.scrollTop = messageContainer.scrollHeight
}

const createCompletion = (message, sourceOfTruth, infoSource, id, courseID) => {
    /**
     * Makes an API request to get a completion from GPT-3, and adds it to the chat log
     * @param {string} message The text to get a completion for
     */
    const history = buildTranscript()
    document.querySelector('#openai_input-'+id).classList.add('disabled')
    document.querySelector('#openai_input-'+id).classList.remove('error')
    document.querySelector('#openai_input-'+id).placeholder = questionString
    document.querySelector('#openai_input-'+id).blur()
    addToChatLog('bot loading', '...', id);

    fetch(`${M.cfg.wwwroot}/blocks/openai_chat_scieneers/api/completion.php`, {
        method: 'POST',
        body: JSON.stringify({
            message: message,
            history: history,
            sourceOfTruth: sourceOfTruth,
            infoSource: infoSource,
			courseID: courseID
        })
    })
    .then(response => {
        let messageContainer = document.querySelector('#openai_chat_scieneers_log-'+id)
        messageContainer.removeChild(messageContainer.lastElementChild)
        document.querySelector('#openai_input-'+id).classList.remove('disabled')

        if (!response.ok) {
            throw Error(response.statusText)
        } else {
            return response.json()
        }
    })
    .then(data => {
        try {
            addToChatLog('bot', data.message, id)
        } catch (error) {
            addToChatLog('bot', data.error.message, id)
        }
    })
    .catch(error => {
        document.querySelector('#openai_input-'+id).classList.add('error')
        document.querySelector('#openai_input-'+id).placeholder = errorString
    })
	
    try {
        let messageCount = 1;

        if(Array.isArray(history)) {
            messageCount = history.length + 1;
        }

        if(typeof _paq !== 'undefined') {
            _paq.push(['trackEvent', 'ChatBot', 'Message Sent', 'ChatBot Message Sent', messageCount]);
        }

        let greeting = document.getElementById("chatbotgreeting");

        if(greeting !== undefined) {
            greeting.style.display = "none";
        }

    } catch(error) {
        console.log("counter error");
    }
}

const buildTranscript = () => {
    /**
     * Using the existing messages in the chat history, create a string that can be used to aid completion
     * @return {JSONObject} A transcript of the conversation up to this point
     */
    let transcript = []
    document.querySelectorAll('.openai_message').forEach((message, index) => {
        if (index === document.querySelectorAll('.openai_message').length - 1) {
            return
        }

        let user = userName
        if (message.classList.contains('bot')) {
            user = assistantName
        }
        transcript.push({"user": user, "message": message.innerText})
    })

    return transcript
}
