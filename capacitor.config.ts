import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'com.diyetisyenai.app',
  appName: 'Diyetisyen AI',
  webDir: 'dist',
  server: {
    androidScheme: 'https'
  }
};

export default config;
