import './i18n';
import { StatusBar } from 'expo-status-bar';
import Constants from 'expo-constants';
import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
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

type Screen = 'home' | 'machines';

export default function App() {
  const { t, i18n } = useTranslation();
  const [currentScreen, setCurrentScreen] = useState<Screen>('home');
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
    if (overrideUrl) return overrideUrl;

    const hostUri = Constants.expoConfig?.hostUri ?? Constants.expoGoConfig?.debuggerHost ?? null;
    if (hostUri) {
      const host = hostUri.split(':')[0];
      return `http://${host}:8000`;
    }

    if (Platform.OS === 'android') return 'http://10.0.2.2:8000';
    return 'http://127.0.0.1:8000';
  }, []);

  const loadMachines = async () => {
    setIsLoading(true);
    setErrorMessage(null);

    try {
      const response = await fetch(`${apiBaseUrl}/api/machines`);
      if (!response.ok) throw new Error(`${t('api_error')}: ${response.status}`);

      const data = (await response.json()) as Machine[];
      setMachines(data);
      setHasLoaded(true);
    } catch (error) {
      setErrorMessage(
        error instanceof Error ? error.message : t('unknown_error')
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
        body: JSON.stringify({ status: newStatus, problem_description: problemDescription }),
      });

      if (!response.ok) throw new Error(`${t('error')}: ${response.status}`);
      const updated = (await response.json()) as Machine;
      setMachines(machines.map(m => m.id === updated.id ? updated : m));
      setShowModal(false);
      Alert.alert(t('success'), t('status_updated'));
    } catch (error) {
      Alert.alert(t('error'), error instanceof Error ? error.message : t('unknown_error'));
    } finally {
      setIsUpdating(false);
    }
  };

  const restoreMachine = async (machine: Machine) => {
    Alert.alert(t('restore_title'), t('restore_message'), [
      { text: t('cancel'), onPress: () => {}, style: 'cancel' },
      {
        text: t('restore_confirm'),
        onPress: async () => {
          setIsUpdating(true);
          try {
            const response = await fetch(`${apiBaseUrl}/api/machines/${machine.id}/restore`, {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
            });

            if (!response.ok) throw new Error(`${t('error')}: ${response.status}`);
            const restored = (await response.json()) as Machine;
            setMachines(machines.map(m => m.id === restored.id ? restored : m));
            Alert.alert(t('success'), t('restore_success'));
          } catch (error) {
            Alert.alert(t('error'), error instanceof Error ? error.message : t('unknown_error'));
          } finally {
            setIsUpdating(false);
          }
        },
      },
    ]);
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
      case 'active': return t('status_active');
      case 'stopped': return t('status_stopped');
      case 'warning': return t('status_warning');
      case 'broken': return t('status_broken');
      default: return status;
    }
  };

  const switchLanguage = (lang: string) => {
    i18n.changeLanguage(lang);
  };

  const Navbar = () => (
    <View style={styles.navbar}>
      <Pressable
        style={styles.navButton}
        onPress={() => currentScreen !== 'home' && setCurrentScreen('home')}
      >
        <Text style={styles.navButtonText}>
          {currentScreen === 'home' ? t('app_title') : t('back')}
        </Text>
      </Pressable>

      <View style={styles.navLanguageGroup}>
        <Pressable
          style={[
            styles.langButton,
            i18n.language === 'hu' && styles.langButtonActive,
          ]}
          onPress={() => switchLanguage('hu')}
        >
          <Text style={styles.langButtonText}>🇭🇺</Text>
        </Pressable>
        <Pressable
          style={[
            styles.langButton,
            i18n.language === 'de' && styles.langButtonActive,
          ]}
          onPress={() => switchLanguage('de')}
        >
          <Text style={styles.langButtonText}>🇩🇪</Text>
        </Pressable>
      </View>
    </View>
  );

  const HomeScreen = () => (
    <ScrollView contentContainerStyle={styles.homeContainer}>
      <Text style={styles.homeTitle}>{t('app_title')}</Text>
      <Text style={styles.homeSubtitle}>Injection Molding ERP System</Text>

      <Pressable
        style={styles.homeButton}
        onPress={() => {
          setCurrentScreen('machines');
          loadMachines();
        }}
      >
        <Text style={styles.homeButtonText}>{t('machines_button_label')}</Text>
      </Pressable>

      <View style={styles.homeInfo}>
        <Text style={styles.homeInfoTitle}>Papirus</Text>
        <Text style={styles.homeInfoText}>
          Injekciós fröccsöntési gyárak intelligens kezelésére kifejlesztett integrált vállalatirányítási rendszer.
        </Text>
      </View>
    </ScrollView>
  );

  const MachinesScreen = () => (
    <View style={styles.container}>
      {isLoading && !hasLoaded && <ActivityIndicator size="large" color="#0f766e" />}

      {errorMessage ? <Text style={styles.errorText}>{errorMessage}</Text> : null}

      {hasLoaded && !isLoading && machines.length === 0 ? (
        <Text style={styles.emptyText}>{t('no_machines')}</Text>
      ) : null}

      <FlatList
        data={machines}
        keyExtractor={(item) => item.id.toString()}
        renderItem={({ item }) => (
          <View style={styles.machineCard}>
            <View style={styles.machineHeader}>
              <Text style={styles.machineName}>{item.name}</Text>
              <View style={[styles.statusBadge, { backgroundColor: getStatusColor(item.status) }]}>
                <Text style={styles.statusBadgeText}>{getStatusLabel(item.status)}</Text>
              </View>
            </View>
            <Text style={styles.machineMeta}>
              {t('machine_type')}: {item.type}
            </Text>
            <Text style={styles.machineMeta}>
              {t('opening')}: {item.open_mm ?? '-'} {t('mm')}
            </Text>
            <Text style={styles.machineMeta}>
              {t('screw_diameter')}: {item.screw_diameter_cm ?? '-'} {t('cm')}
            </Text>
            {item.problem_description && (
              <Text style={styles.problemText}>
                {t('problem')}: {item.problem_description}
              </Text>
            )}
            <View style={styles.buttonGroup}>
              <Pressable
                style={[styles.actionButton, { backgroundColor: '#0f766e' }]}
                onPress={() => openStatusModal(item)}
              >
                <Text style={styles.actionButtonText}>{t('change_status')}</Text>
              </Pressable>
              {item.status === 'broken' && (
                <Pressable
                  style={[styles.actionButton, { backgroundColor: '#059669' }]}
                  onPress={() => restoreMachine(item)}
                >
                  <Text style={styles.actionButtonText}>{t('restore')}</Text>
                </Pressable>
              )}
            </View>
          </View>
        )}
        contentContainerStyle={styles.listContent}
      />
    </View>
  );

  return (
    <SafeAreaView style={styles.safeArea}>
      <Navbar />

      {currentScreen === 'home' ? <HomeScreen /> : <MachinesScreen />}

      <Modal visible={showModal} animationType="slide" transparent={true}>
        <SafeAreaView style={styles.modalContainer}>
          <ScrollView style={styles.modalContent}>
            <Text style={styles.modalTitle}>{t('change_status')}</Text>
            {selectedMachine && (
              <>
                <Text style={styles.modalMachineName}>{selectedMachine.name}</Text>

                <Text style={styles.modalLabel}>{t('new_status')}:</Text>
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
                    <Text style={styles.statusOptionText}>{getStatusLabel(status)}</Text>
                  </Pressable>
                ))}

                {['warning', 'broken'].includes(newStatus) && (
                  <>
                    <Text style={styles.modalLabel}>{t('problem_description')}:</Text>
                    <TextInput
                      style={styles.problemInput}
                      placeholder={t('problem_description_placeholder')}
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
                    <Text style={styles.modalButtonText}>{t('cancel')}</Text>
                  </Pressable>
                  <Pressable
                    style={[styles.modalButton, { backgroundColor: '#22c55e' }]}
                    onPress={updateMachineStatus}
                    disabled={isUpdating}
                  >
                    <Text style={styles.modalButtonText}>
                      {isUpdating ? t('updating') : t('update')}
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
  safeArea: {
    flex: 1,
    backgroundColor: '#f1f5f9',
  },
  navbar: {
    backgroundColor: '#0f766e',
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: 2,
    borderBottomColor: '#0d5f59',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.15,
    shadowRadius: 3,
    elevation: 5,
  },
  navButton: {
    paddingVertical: 8,
    paddingHorizontal: 16,
    borderRadius: 8,
    backgroundColor: 'rgba(255, 255, 255, 0.2)',
  },
  navButtonText: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: '600',
  },
  navLanguageGroup: {
    flexDirection: 'row',
    gap: 8,
  },
  langButton: {
    paddingVertical: 8,
    paddingHorizontal: 12,
    borderRadius: 8,
    backgroundColor: 'rgba(255, 255, 255, 0.2)',
    borderWidth: 2,
    borderColor: 'transparent',
  },
  langButtonActive: {
    borderColor: '#ffffff',
    backgroundColor: 'rgba(255, 255, 255, 0.3)',
  },
  langButtonText: {
    fontSize: 18,
  },
  container: {
    flex: 1,
    paddingHorizontal: 16,
    paddingTop: 16,
  },
  homeContainer: {
    flexGrow: 1,
    paddingHorizontal: 16,
    paddingTop: 32,
    paddingBottom: 32,
    justifyContent: 'center',
    alignItems: 'center',
  },
  homeTitle: {
    fontSize: 36,
    fontWeight: '800',
    color: '#0f172a',
    marginBottom: 8,
    textAlign: 'center',
  },
  homeSubtitle: {
    fontSize: 16,
    color: '#64748b',
    marginBottom: 32,
    textAlign: 'center',
    fontWeight: '500',
  },
  homeButton: {
    backgroundColor: '#0f766e',
    borderRadius: 16,
    paddingVertical: 18,
    paddingHorizontal: 48,
    marginBottom: 32,
    shadowColor: '#0f766e',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.2,
    shadowRadius: 8,
    elevation: 5,
  },
  homeButtonText: {
    color: '#ffffff',
    fontSize: 20,
    fontWeight: '700',
    textAlign: 'center',
  },
  homeInfo: {
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 20,
    borderWidth: 1,
    borderColor: '#e2e8f0',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 2,
    maxWidth: 400,
    width: '100%',
  },
  homeInfoTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#0f766e',
    marginBottom: 8,
  },
  homeInfoText: {
    fontSize: 14,
    color: '#475569',
    lineHeight: 22,
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
    marginBottom: 12,
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
    textAlign: 'center',
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
