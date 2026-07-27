import { StatusBar } from 'expo-status-bar';
import Constants from 'expo-constants';
import { useMemo, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  Platform,
  Pressable,
  SafeAreaView,
  StyleSheet,
  Text,
  View,
} from 'react-native';

type Machine = {
  id: number;
  name: string;
  type: string;
  open_mm: number | null;
  screw_diameter_cm: number | null;
  status: string;
};

export default function App() {
  const [machines, setMachines] = useState<Machine[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [hasLoaded, setHasLoaded] = useState(false);

  const apiBaseUrl = useMemo(() => {
    const overrideUrl = process.env.EXPO_PUBLIC_API_URL;

    if (overrideUrl) {
      return overrideUrl;
    }

    const hostUri =
      Constants.expoConfig?.hostUri ??
      Constants.expoGoConfig?.debuggerHost ??
      null;

    if (hostUri) {
      const host = hostUri.split(':')[0];
      return `http://${host}:8000`;
    }

    if (Platform.OS === 'android') {
      // Android emulator fallback.
      return 'http://10.0.2.2:8000';
    }

    // iOS simulator / local fallback.
    return 'http://127.0.0.1:8000';
  }, []);

  const loadMachines = async () => {
    setIsLoading(true);
    setErrorMessage(null);

    try {
      const response = await fetch(`${apiBaseUrl}/api/machines`);

      if (!response.ok) {
        throw new Error(`API hiba: ${response.status}`);
      }

      const data = (await response.json()) as Machine[];
      setMachines(data);
      setHasLoaded(true);
    } catch (error) {
      setErrorMessage(
        error instanceof Error
          ? `${error.message} (API: ${apiBaseUrl}/api/machines)`
          : 'Ismeretlen hiba tortent.',
      );
    } finally {
      setIsLoading(false);
    }
  };

  const renderMachine = ({ item }: { item: Machine }) => {
    return (
      <View style={styles.machineCard}>
        <Text style={styles.machineName}>{item.name}</Text>
        <Text style={styles.machineMeta}>{item.type}</Text>
        <Text style={styles.machineMeta}>Nyitas: {item.open_mm ?? '-'} mm</Text>
        <Text style={styles.machineMeta}>
          Csigaatmero: {item.screw_diameter_cm ?? '-'} cm
        </Text>
        <Text style={styles.machineStatus}>Statusz: {item.status}</Text>
      </View>
    );
  };

  return (
    <SafeAreaView style={styles.container}>
      <Text style={styles.title}>Papirus</Text>

      <Pressable style={styles.button} onPress={loadMachines} disabled={isLoading}>
        <Text style={styles.buttonText}>{isLoading ? 'Betoltes...' : 'Gepek'}</Text>
      </Pressable>

      {isLoading && <ActivityIndicator size="large" color="#0f766e" />}

      {errorMessage ? <Text style={styles.errorText}>{errorMessage}</Text> : null}

      {hasLoaded && !isLoading && machines.length === 0 ? (
        <Text style={styles.emptyText}>Nincs gep az adatbazisban.</Text>
      ) : null}

      <FlatList
        data={machines}
        keyExtractor={(item) => item.id.toString()}
        renderItem={renderMachine}
        contentContainerStyle={styles.listContent}
      />

      <StatusBar style="auto" />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f1f5f9',
    paddingHorizontal: 16,
    paddingTop: 16,
  },
  title: {
    fontSize: 28,
    fontWeight: '700',
    color: '#0f172a',
    marginBottom: 16,
  },
  button: {
    backgroundColor: '#0f766e',
    borderRadius: 12,
    paddingVertical: 14,
    alignItems: 'center',
    marginBottom: 16,
  },
  buttonText: {
    color: '#ffffff',
    fontSize: 18,
    fontWeight: '600',
  },
  listContent: {
    paddingBottom: 24,
    gap: 12,
  },
  machineCard: {
    backgroundColor: '#ffffff',
    borderRadius: 14,
    padding: 14,
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  machineName: {
    fontSize: 17,
    fontWeight: '700',
    color: '#0f172a',
    marginBottom: 4,
  },
  machineMeta: {
    fontSize: 14,
    color: '#334155',
  },
  machineStatus: {
    marginTop: 8,
    fontSize: 13,
    fontWeight: '600',
    color: '#0f766e',
  },
  errorText: {
    color: '#b91c1c',
    marginBottom: 10,
    fontWeight: '500',
  },
  emptyText: {
    color: '#334155',
    marginBottom: 10,
  },
});
