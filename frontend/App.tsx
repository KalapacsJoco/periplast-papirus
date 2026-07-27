import { StatusBar } from 'expo-status-bar';
import Constants from 'expo-constants';
import { useMemo, useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  FlatList,
  Modal,
  Platform,
  Pressable,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';

type Machine = {
  id: number;
  name: string;
  type: string;
  open_mm: number | null;
  screw_diameter_cm: number | null;
  status: string;
  problem_description?: string;
};

export default function App() {
  const [machines, setMachines] = useState<Machine[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [hasLoaded, setHasLoaded] = useState(false);
  const [selectedMachine, setSelectedMachine] = useState<Machine | null>(null);
  const [showModal, setShowModal] = useState(false);
  const [newStatus, setNewStatus] = useState<string>('');
  const [problemDescription, setProblemDescription] = useState<string>('');
  const [isUpdating, setIsUpdating] = useState(false);

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

  const openStatusModal = (machine: Machine) => {
    setSelectedMachine(machine);
    setNewStatus(machine.status);
    setProblemDescription(machine.problem_description || '');
    setShowModal(true);
  };

  const updateMachineStatus = async () => {
    if (!selectedMachine) return;

    setIsUpdating(true);
    try {
      const response = await fetch(`${apiBaseUrl}/api/machines/${selectedMachine.id}/status`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          status: newStatus,
          problem_description: problemDescription,
        }),
      });

      if (!response.ok) {
        throw new Error(`Status frissítés sikertelen: ${response.status}`);
      }

      const updated = (await response.json()) as Machine;
      setMachines(machines.map(m => m.id === updated.id ? updated : m));
      setShowModal(false);
      Alert.alert('Siker', 'A gép statusza frissítve lett.');
    } catch (error) {
      Alert.alert(
        'Hiba',
        error instanceof Error ? error.message : 'Ismeretlen hiba tortent.'
      );
    } finally {
      setIsUpdating(false);
    }
  };

  const restoreMachine = async (machine: Machine) => {
    Alert.alert(
      'Gép üzemen kívül helyezése vége',
      'Biztos vagy-e, hogy reaktíválja a gépet?',
      [
        { text: 'Mégse', onPress: () => {}, style: 'cancel' },
        {
          text: 'Igen, reaktívál',
          onPress: async () => {
            setIsUpdating(true);
            try {
              const response = await fetch(`${apiBaseUrl}/api/machines/${machine.id}/restore`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
              });

              if (!response.ok) {
                throw new Error(`Helyreállítás sikertelen: ${response.status}`);
              }

              const restored = (await response.json()) as Machine;
              setMachines(machines.map(m => m.id === restored.id ? restored : m));
              Alert.alert('Siker', 'A gép reaktívlódott.');
            } catch (error) {
              Alert.alert(
                'Hiba',
                error instanceof Error ? error.message : 'Ismeretlen hiba tortent.'
              );
            } finally {
              setIsUpdating(false);
            }
          },
        },
      ],
      { cancelable: false }
    );
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'active': return '#22c55e';
      case 'stopped': return '#999999';
      case 'warning': return '#f59e0b';
      case 'broken': return '#ef4444';
      default: return '#999999';
    }
  };

  const getStatusLabel = (status: string) => {
    switch (status) {
      case 'active': return 'Aktív';
      case 'stopped': return 'Leállítva';
      case 'warning': return 'Figyelmeztetés';
      case 'broken': return 'Törött';
      default: return status;
    }
  };

  const renderMachine = ({ item }: { item: Machine }) => {
    const statusColor = getStatusColor(item.status);
    return (
      <View style={styles.machineCard}>
        <View style={styles.machineHeader}>
          <Text style={styles.machineName}>{item.name}</Text>
          <View
            style={[
              styles.statusBadge,
              { backgroundColor: statusColor },
            ]}
          >
            <Text style={styles.statusBadgeText}>{getStatusLabel(item.status)}</Text>
          </View>
        </View>
        <Text style={styles.machineMeta}>{item.type}</Text>
        <Text style={styles.machineMeta}>Nyitas: {item.open_mm ?? '-'} mm</Text>
        <Text style={styles.machineMeta}>
          Csigaatmero: {item.screw_diameter_cm ?? '-'} cm
        </Text>
        {item.problem_description && (
          <Text style={styles.problemText}>
            Probléma: {item.problem_description}
          </Text>
        )}
        <View style={styles.buttonGroup}>
          <Pressable
            style={[styles.actionButton, { backgroundColor: '#0f766e' }]}
            onPress={() => openStatusModal(item)}
          >
            <Text style={styles.actionButtonText}>Statusz módosítás</Text>
          </Pressable>
          {item.status === 'broken' && (
            <Pressable
              style={[styles.actionButton, { backgroundColor: '#059669' }]}
              onPress={() => restoreMachine(item)}
            >
              <Text style={styles.actionButtonText}>Reaktívál</Text>
            </Pressable>
          )}
        </View>
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

      <Modal visible={showModal} animationType="slide" transparent={true}>
        <SafeAreaView style={styles.modalContainer}>
          <ScrollView style={styles.modalContent}>
            <Text style={styles.modalTitle}>Statusz módosítás</Text>
            {selectedMachine && (
              <>
                <Text style={styles.modalMachineName}>{selectedMachine.name}</Text>

                <Text style={styles.modalLabel}>Új statusz:</Text>
                {['active', 'stopped', 'warning', 'broken'].map((status) => (
                  <Pressable
                    key={status}
                    style={[
                      styles.statusOption,
                      newStatus === status && styles.statusOptionSelected,
                    ]}
                    onPress={() => setNewStatus(status)}
                  >
                    <View
                      style={[
                        styles.statusOptionDot,
                        { backgroundColor: getStatusColor(status) },
                      ]}
                    />
                    <Text style={styles.statusOptionText}>
                      {getStatusLabel(status)}
                    </Text>
                  </Pressable>
                ))}

                {['warning', 'broken'].includes(newStatus) && (
                  <>
                    <Text style={styles.modalLabel}>Probléma leírása:</Text>
                    <TextInput
                      style={styles.problemInput}
                      placeholder="Írja le a problémát..."
                      value={problemDescription}
                      onChangeText={setProblemDescription}
                      multiline={true}
                      numberOfLines={4}
                    />
                  </>
                )}

                <View style={styles.modalButtonGroup}>
                  <Pressable
                    style={[styles.modalButton, { backgroundColor: '#ef4444' }]}
                    onPress={() => setShowModal(false)}
                    disabled={isUpdating}
                  >
                    <Text style={styles.modalButtonText}>Mégsem</Text>
                  </Pressable>
                  <Pressable
                    style={[styles.modalButton, { backgroundColor: '#22c55e' }]}
                    onPress={updateMachineStatus}
                    disabled={isUpdating}
                  >
                    <Text style={styles.modalButtonText}>
                      {isUpdating ? 'Frissítés...' : 'Frissítés'}
                    </Text>
                  </Pressable>
                </View>
              </>
            )}
          </ScrollView>
        </SafeAreaView>
      </Modal>

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
  machineHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  machineName: {
    fontSize: 17,
    fontWeight: '700',
    color: '#0f172a',
    flex: 1,
  },
  statusBadge: {
    marginLeft: 8,
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 8,
  },
  statusBadgeText: {
    color: '#ffffff',
    fontSize: 12,
    fontWeight: '600',
  },
  machineMeta: {
    fontSize: 14,
    color: '#334155',
    marginVertical: 2,
  },
  problemText: {
    fontSize: 13,
    color: '#dc2626',
    fontWeight: '500',
    marginTop: 8,
    fontStyle: 'italic',
  },
  buttonGroup: {
    flexDirection: 'row',
    gap: 8,
    marginTop: 12,
  },
  actionButton: {
    flex: 1,
    paddingVertical: 10,
    paddingHorizontal: 12,
    borderRadius: 8,
    alignItems: 'center',
  },
  actionButtonText: {
    color: '#ffffff',
    fontSize: 13,
    fontWeight: '600',
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
  modalContainer: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.5)',
  },
  modalContent: {
    backgroundColor: '#ffffff',
    margin: 16,
    borderRadius: 16,
    padding: 20,
    flex: 1,
  },
  modalTitle: {
    fontSize: 22,
    fontWeight: '700',
    color: '#0f172a',
    marginBottom: 4,
  },
  modalMachineName: {
    fontSize: 16,
    color: '#0f766e',
    fontWeight: '600',
    marginBottom: 16,
  },
  modalLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: '#334155',
    marginTop: 16,
    marginBottom: 8,
  },
  statusOption: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
    paddingHorizontal: 12,
    marginBottom: 8,
    borderRadius: 8,
    borderWidth: 2,
    borderColor: '#e2e8f0',
  },
  statusOptionSelected: {
    borderColor: '#0f766e',
    backgroundColor: '#ecf9f8',
  },
  statusOptionDot: {
    width: 16,
    height: 16,
    borderRadius: 8,
    marginRight: 12,
  },
  statusOptionText: {
    fontSize: 15,
    color: '#0f172a',
    fontWeight: '500',
  },
  problemInput: {
    borderWidth: 1,
    borderColor: '#e2e8f0',
    borderRadius: 8,
    padding: 12,
    fontSize: 14,
    color: '#0f172a',
    textAlignVertical: 'top',
    minHeight: 100,
  },
  modalButtonGroup: {
    flexDirection: 'row',
    gap: 12,
    marginTop: 24,
    marginBottom: 24,
  },
  modalButton: {
    flex: 1,
    paddingVertical: 12,
    borderRadius: 8,
    alignItems: 'center',
  },
  modalButtonText: {
    color: '#ffffff',
    fontSize: 15,
    fontWeight: '600',
  },
});

