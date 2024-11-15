import Cookies from 'js-cookie';
import React, { createContext, useState, useEffect } from 'react';
import { api } from '../../utils';
import { JWT } from '../../types';
import { differenceInSeconds } from 'date-fns';

interface SessionContextType {
  isAuthenticated: boolean;
  setIsAuthenticated: React.Dispatch<React.SetStateAction<boolean>>;
}

const isExpNear = (timestamp: number): boolean => {
  const now = Date.now();
  const diffInSeconds = Math.abs(differenceInSeconds(now, timestamp));
  return diffInSeconds <= 60;
};

export const SessionContext = createContext<SessionContextType | null>(null);

export const SessionProvider = ({ children }: { children: React.ReactNode }) => {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [isLoading, setIsLoading] = useState(true);

  const token = Cookies.get('jwt') ?? false;
  const jwtExp = Cookies.get('np_jwt_exp') ?? false;
  
  useEffect(() => {
    if (!token) {
      window.location.href = '/wp-admin';
    }else{
      setIsLoading(false);
      return;
    }

    const validateToken = async () => {
      try {
        const {data} = await api.get<JWT>('auth/validate');

        if (data?.exp && data?.iat && data?.uid && data?.iss && token) {
          setIsAuthenticated(true);
          Cookies.set('jwt', token, { path: '/', sameSite: 'Strict' });
        } else {
          setIsAuthenticated(false);
          Cookies.remove('jwt');
          window.location.href = '/wp-admin'; 
        }
      } catch (error) {
        setIsAuthenticated(false);
        Cookies.remove('jwt');
        window.location.href = '/wp-admin'; 
      } finally {
        setIsLoading(false);
      }
    };

    if(jwtExp && isExpNear(+jwtExp)) validateToken();
  }, [token, jwtExp]);

  if (isLoading) {
    return null
  }

  return (
    <SessionContext.Provider value={{ isAuthenticated, setIsAuthenticated }}>
      {children}
    </SessionContext.Provider>
  );
};
