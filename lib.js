var questionString = 'Ask a question...'
var errorString = 'An error occurred! Please try again later.'

const init = (Y, sourceOfTruth, infoSource, id) => {
    loadChatHistory(id, kiCampusChatBotCourseID)
    // Floating chat bubble (bottom right) that opens the block drawer.
    const title = document.querySelector('#inst'+id+' .card-title, #inst'+id+' h5')?.textContent?.trim() || 'Chat';
    if (!document.querySelector('#scieneers-chat-bubble-'+id)) {
        document.body.insertAdjacentHTML('beforeend', `
            <div class="scieneers-chat-bubble" id="scieneers-chat-bubble-${id}" title="${title}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="28" height="28">
                    <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H5.17L4 17.17V4h16v12z"/>
                    <path d="M7 9h2v2H7zm4 0h2v2h-2zm4 0h2v2h-2z"/>
                </svg>
            </div>
        `)
        document.querySelector('#scieneers-chat-bubble-'+id).addEventListener('click', () => {
            const block = document.querySelector('#inst'+id)

            const toggle = document.querySelector('[data-toggler="drawers"][data-action="toggle"][data-target="theme_boost-drawers-blocks"]')
                || document.querySelector('[data-toggler="drawers"][data-action="toggle"][data-target$="drawers-blocks"]')
            if (toggle) {
                toggle.click()
            }
            if (block) {
                block.scrollIntoView({behavior: 'smooth', block: 'start'})
            }
        })

        const bubble = document.querySelector('#scieneers-chat-bubble-'+id)
        const drawer = document.querySelector('#theme_boost-drawers-blocks')
            || document.querySelector('[id$="drawers-blocks"]')
        const syncBubble = () => {
            bubble.style.display = (drawer && drawer.classList.contains('show')) ? 'none' : ''
        }
        if (drawer) {
            new MutationObserver(syncBubble).observe(drawer, {attributes: true, attributeFilter: ['class']})
        }
        syncBubble()
    }

    document.querySelector('#openai_input-' + id).addEventListener('keyup', e => {
    if (e.which === 13 &&
        e.target.value.trim() !== '' &&
        !e.target.classList.contains('disabled')) {
            addToChatLog('user', e.target.value, id);
            createCompletion(
                e.target.value,
                sourceOfTruth,
                infoSource,
                id,
                kiCampusChatBotCourseID,
                false
            );
            e.target.value = '';
        }
    });

    const sendBtn = document.querySelector('#openai_send-'+id)
    if (sendBtn) {
        sendBtn.addEventListener('click', () => {
            const input = document.querySelector('#openai_input-'+id)

            if (
                !input ||
                input.value.trim() === '' ||
                input.classList.contains('disabled')
            ) {
                return
            }

            addToChatLog('user', input.value, id)
            createCompletion(input.value, sourceOfTruth, infoSource, id, kiCampusChatBotCourseID)
            input.value = ''
        })
    }

    // Lernmodus-Zustand im Frontend speichern.
    const socraticToggle = document.querySelector('#scieneers-socratic-toggle-' + id)
    const socraticStorageKey = 'scieneers_socratic_active'

    if (socraticToggle) {
        // Zustand nach Reload wiederherstellen.
        socraticToggle.checked = sessionStorage.getItem(socraticStorageKey) === 'true'

        socraticToggle.addEventListener('change', () => {
            if (socraticToggle.checked) {
                sessionStorage.setItem(socraticStorageKey, 'true')

                addToChatLog('user', 'Starte den Lernmodus', id)
                createCompletion(
                    'Starte den Lernmodus',
                    sourceOfTruth,
                    infoSource,
                    id,
                    kiCampusChatBotCourseID,
                    true
                )
            } else {
                sessionStorage.removeItem(socraticStorageKey)

                addToChatLog('user', 'Beende den Lernmodus', id)
                createCompletion(
                    'Beende den Lernmodus',
                    sourceOfTruth,
                    infoSource,
                    id,
                    kiCampusChatBotCourseID,
                    false
                )
            }
        })
    }

    const block = document.querySelector('#inst' + id);
    const fullscreenBtn = document.querySelector('#scieneers-fullscreen-' + id);

    if (block && fullscreenBtn) {
        const placeholder = document.createComment('chat-position');

        fullscreenBtn.addEventListener('click', () => {
            const active = block.classList.contains('scieneers-fullscreen');

            if (active) {
                placeholder.replaceWith(block);
                block.classList.remove('scieneers-fullscreen');
            } else {
                block.before(placeholder);
                document.body.appendChild(block);
                block.classList.add('scieneers-fullscreen');
            }

            const icon = fullscreenBtn.querySelector('i');
            icon.className = active ? 'fa fa-expand' : 'fa fa-compress';

            fullscreenBtn.title = active ? 'Vergrößern' : 'Verkleinern';
            fullscreenBtn.setAttribute('aria-label', fullscreenBtn.title);
        });
    }

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

const addToChatLog = (type, message, id, skipPersist) => {
    /**
     * Add a message to the chat UI
     * @param {string} type Which side of the UI the message should be on. Can be "user" or "bot"
     * @param {string} message The text of the message to add
     * @param {boolean} skipPersist If true, do not write to sessionStorage (used when rendering from storage)
     */
    let messageContainer = document.querySelector('#openai_chat_scieneers_log-'+id)
    messageContainer.insertAdjacentHTML('beforeend', `
        <div class='openai_message ${type}'>
            ${message}
        </div>
    `)
    messageContainer.scrollTop = messageContainer.scrollHeight

    if (!skipPersist) {
        persistChatMessage(type, message)
    }
}

const chatStorageKey = () => 'kic_chat_log_course' + kiCampusChatBotCourseID

const normalizeChatMessage = (role, content) =>
    JSON.stringify([role, (content || '').replace(/\s+/g, ' ').trim()])

const loadChatLogFromStorage = () => {
    try {
        const raw = sessionStorage.getItem(chatStorageKey())
        if (!raw) {
            return []
        }
        const parsed = JSON.parse(raw)
        return Array.isArray(parsed) ? parsed : []
    } catch (e) {
        return []
    }
}

const persistChatMessage = (type, message) => {
    try {
        const role = type === 'user' ? 'user' : 'assistant'
        const log = loadChatLogFromStorage()
        log.push({role: role, content: message})
        sessionStorage.setItem(chatStorageKey(), JSON.stringify(log))
    } catch (e) {
        // sessionStorage voll oder nicht verfügbar – Chat bleibt trotzdem nutzbar.
    }
}

const renderChatContent = (container, content) => {
    const parsed = new DOMParser().parseFromString(content, 'text/html');
    const allowedTags = ['A', 'BR', 'P', 'STRONG', 'EM', 'B', 'I', 'UL', 'OL', 'LI'];

    const createSafeNode = node => {
        if (node.nodeType === Node.TEXT_NODE) {
            return document.createTextNode(node.textContent);
        }

        if (node.nodeType !== Node.ELEMENT_NODE) {
            return document.createDocumentFragment();
        }

        const tag = node.tagName;
        const element = allowedTags.includes(tag)
            ? document.createElement(tag.toLowerCase())
            : document.createDocumentFragment();

        if (tag === 'A' && element instanceof HTMLAnchorElement) {
            const href = node.getAttribute('href');

            try {
                const url = new URL(href, window.location.href);

                if (['http:', 'https:'].includes(url.protocol)) {
                    element.href = url.href;
                    element.target = '_blank';
                    element.rel = 'noopener noreferrer';
                }
            } catch (error) {
                // Ungültige URL ignorieren.
            }
        }

        node.childNodes.forEach(child => {
            element.appendChild(createSafeNode(child));
        });

        return element;
    };

    container.replaceChildren();

    parsed.body.childNodes.forEach(node => {
        container.appendChild(createSafeNode(node));
    });
};

const updateChatResponse = (element, message) => {
    renderChatContent(element, message);
    element.classList.remove('loading');

    const messageContainer = element.parentElement;
    messageContainer.scrollTop = messageContainer.scrollHeight;
};

const readChatStream = async (response, element) => {
    if (!response.body) {
        throw new Error('Streaming wird vom Browser nicht unterstützt');
    }

    const reader = response.body.getReader();
    const decoder = new TextDecoder();

    let buffer = '';
    let message = '';
    let finished = false;

    const processLine = line => {
        if (!line.trim()) {
            return;
        }

        const event = JSON.parse(line);

        switch (event.type) {
            case 'meta':
                // Die thread_id wird bereits serverseitig gespeichert.
                break;

            case 'token':
                message += event.token || '';
                updateChatResponse(element, message);
                break;

            case 'final':
                // Verbindliche Antwort ersetzt die gestreamten Tokens.
                message = event.message || '';
                updateChatResponse(element, message);
                finished = true;
                break;

            case 'error':
                throw new Error(event.message || 'API-Fehler');
        }
    };

    while (true) {
        const {value, done} = await reader.read();

        if (done) {
            break;
        }

        buffer += decoder.decode(value, {stream: true});

        let newline;

        while ((newline = buffer.indexOf('\n')) !== -1) {
            const line = buffer.slice(0, newline);
            buffer = buffer.slice(newline + 1);

            processLine(line);
        }
    }

    buffer += decoder.decode();

    if (buffer.trim()) {
        processLine(buffer);
    }

    if (!finished) {
        throw new Error('Stream wurde ohne finale Antwort beendet');
    }

    return message;
};

const createCompletion = async (
    message,
    sourceOfTruth,
    infoSource,
    id,
    courseID,
    startSocraticV2 = false
) => {
    const input = document.querySelector('#openai_input-' + id);
    const sendButton = document.querySelector('#openai_send-' + id);
    const messageContainer = document.querySelector(
        '#openai_chat_scieneers_log-' + id
    );

    // Verhindert parallele Requests innerhalb desselben Chats.
    if (input.classList.contains('disabled')) {
        return;
    }

    const history = buildTranscript();

    input.classList.add('disabled');
    input.classList.remove('error');
    input.placeholder = questionString;
    input.blur();

    if (sendButton) {
        sendButton.disabled = true;
    }

    // Eine einzige Chatnachricht erzeugen und laufend aktualisieren.
    const botMessage = document.createElement('div');
    botMessage.className = 'openai_message bot loading';
    botMessage.textContent = '...';

    messageContainer.appendChild(botMessage);
    messageContainer.scrollTop = messageContainer.scrollHeight;

    const requestBody = {
        message: message,
        history: history,
        sourceOfTruth: sourceOfTruth,
        infoSource: infoSource,
        courseID: courseID,
        moduleIDs: kiCampusChatBotModuleIDs
    };

    if (startSocraticV2) {
        requestBody.startSocraticV2 = true;
    }

    try {
        const response = await fetch(
            `${M.cfg.wwwroot}/blocks/openai_chat_scieneers/api/completion.php`,
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(requestBody)
            }
        );

        if (!response.ok) {
            const errorBody = await response.text();

            throw new Error(
                `HTTP ${response.status}: ${errorBody || response.statusText}`
            );
        }

        const contentType = response.headers.get('content-type') || '';
        let answer;

        if (contentType.includes('application/x-ndjson')) {
            // Streaming-Endpunkt.
            answer = await readChatStream(response, botMessage);
        } else {
            // Normaler Endpunkt: vollständige JSON-Antwort.
            const data = await response.json();

            if (typeof data.message !== 'string') {
                throw new Error(data.error || 'Ungültige API-Antwort');
            }

            answer = data.message;
            updateChatResponse(botMessage, answer);
        }

        // Antwort erst nach erfolgreichem Abschluss speichern.
        persistChatMessage('bot', answer);

    } catch (error) {
        console.error('Chat completion error:', error);

        input.classList.add('error');
        input.placeholder = errorString;

        // Eventuelle Teilantwort nicht als vollständige Antwort behalten.
        updateChatResponse(
            botMessage,
            'Die Antwort konnte nicht vollständig geladen werden. ' +
            'Bitte versuche es erneut.'
        );

    } finally {
        input.classList.remove('disabled');

        if (sendButton) {
            sendButton.disabled = false;
        }
    }

    // Bestehendes Matomo-Tracking beibehalten.
    try {
        let messageCount = 1;

        if (Array.isArray(history)) {
            messageCount = history.length + 1;
        }

        if (typeof _paq !== 'undefined') {
            _paq.push([
                'trackEvent',
                'ChatBot',
                'Message Sent',
                'ChatBot Message Sent',
                messageCount
            ]);
        }

        const greeting = document.getElementById('chatbotgreeting');

        if (greeting) {
            greeting.style.display = 'none';
        }

    } catch (error) {
        console.error('Chat tracking error:', error);
    }
};

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

const loadChatHistory = (id, courseID) => {
    const storedLog = loadChatLogFromStorage()
    const renderedKeys = new Set()

    storedLog.forEach(message => {
        const type = message.role === 'user' ? 'user' : 'bot'
        addToChatLog(type, message.content, id, true)
        renderedKeys.add(normalizeChatMessage(message.role, message.content))
    })

    fetch(
        `${M.cfg.wwwroot}/blocks/openai_chat_scieneers/api/history.php?courseid=${courseID}`
    )
    .then(response => {
        if (!response.ok) {
            throw new Error('Could not load chat history')
        }

        return response.json()
    })
    .then(data => {
        if (!Array.isArray(data.messages)) {
            return
        }

        const messageContainer =
            document.querySelector('#openai_chat_scieneers_log-' + id)

        if (!messageContainer) {
            return
        }

        data.messages.forEach(message => {
            const key = normalizeChatMessage(message.role, message.content)
            if (renderedKeys.has(key)) {
                return
            }
            renderedKeys.add(key)

            const type = message.role === 'user' ? 'user' : 'bot'
            addToChatLog(type, message.content, id)
        })
    })
    .catch(error => {
        console.error('Chat history could not be loaded:', error)
    })
}