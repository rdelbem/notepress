import { useEffect } from 'react';
import Cookies from 'js-cookie';

const JwtListener = () => {
    useEffect(() => {
        const eventSource = new EventSource(`/wp-json/notepress-api/send-jwt?userId=${window.user.id}`);  

        eventSource.onmessage = function (event) {
            const data = JSON.parse(event.data);
            if (data.jwt) {
                Cookies.set('jwt', data.jwt, { path: '/', secure: true, sameSite: 'Strict' });
            }
        };

        eventSource.onerror = function (error) {
            console.error('Error receiving JWT via SSE:', error);
            eventSource.close();
        };

        return () => {
            eventSource.close();
        };
    }, []);

    return null;
};

export default JwtListener;
