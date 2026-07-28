import './i18n';
import { StatusBar } from 'expo-status-bar';
import Constants from 'expo-constants';
import { useEffect, useMemo, useState } from 'react';
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

type Order = {
  id: number;
  order_number: string;
  machine_id: number;
  mould_id: number;
  product_id: number;
  date?: string;
  customer?: string;
  quantity: number;
  current_quantity?: number;
  started_at?: string | null;
  cycle_time: number | null;
  net_weight: string | number | null;
  gross_weight: string | number | null;
  color_percentage: string | number | null;
  status: string;
  machine?: { id: number; name: string; status?: string; problem_description?: string };
  mould?: { id: number; name: string; number: string; cavity_count?: number | null; status?: string; problem_description?: string };
  product?: { id: number; name: string; code: string; is_active?: boolean };
};

type Mould = {
  id: number;
  name: string;
  type: string;
  number: string;
  cavity_count: number | null;
  open_mm: number | null;
  status: string;
  problem_description?: string;
  product?: { id: number; name: string };
};

type Product = {
  id: number;
  name: string;
  code: string;
  is_active?: boolean;
  material?: { id: number; name: string; code: string };
  color?: { id: number; name: string; code: string };
  additives?: Array<{ id: number; name: string; code: string }>;
  moulds?: Array<{ id: number; name: string; number: string }>;
};

type DashboardMachine = Machine & {
  currentOrder: Order | null;
};

type Screen = 'machines' | 'moulds' | 'products';
type IssueTarget = 'machine' | 'mould' | 'product';

export default function App() {
  const { t, i18n } = useTranslation();
  const [currentScreen, setCurrentScreen] = useState<Screen>('machines');
  const [machines, setMachines] = useState<Machine[]>([]);
  const [orders, setOrders] = useState<Order[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [isOrdersLoading, setIsOrdersLoading] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [ordersError, setOrdersError] = useState<string | null>(null);
  const [hasLoaded, setHasLoaded] = useState(false);
  const [ordersLoaded, setOrdersLoaded] = useState(false);
  const [productionDrafts, setProductionDrafts] = useState<Record<number, { cycle_time: string; quantity: string; current_quantity: string }>>({});
  const [selectedMachine, setSelectedMachine] = useState<Machine | null>(null);
  const [showModal, setShowModal] = useState(false);
  const [showMenu, setShowMenu] = useState(false);
  const [issueTarget, setIssueTarget] = useState<IssueTarget>('machine');
  const [newStatus, setNewStatus] = useState<string>('');
  const [problemDescription, setProblemDescription] = useState<string>('');
  const [isUpdating, setIsUpdating] = useState(false);

  // Moulds state
  const [moulds, setMoulds] = useState<Mould[]>([]);
  const [isMouldsLoading, setIsMouldsLoading] = useState(false);
  const [mouldsError, setMouldsError] = useState<string | null>(null);
  const [mouldsLoaded, setMouldsLoaded] = useState(false);
  const [selectedMould, setSelectedMould] = useState<Mould | null>(null);
  const [showMouldModal, setShowMouldModal] = useState(false);
  const [newMouldStatus, setNewMouldStatus] = useState<string>('');
  const [mouldProblemDescription, setMouldProblemDescription] = useState<string>('');
  const [isMouldUpdating, setIsMouldUpdating] = useState(false);

  // Products state
  const [products, setProducts] = useState<Product[]>([]);
  const [isProductsLoading, setIsProductsLoading] = useState(false);
  const [productsError, setProductsError] = useState<string | null>(null);
  const [productsLoaded, setProductsLoaded] = useState(false);

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

  useEffect(() => {
    loadMachines();
    loadOrders();
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

  const loadOrders = async () => {
    setIsOrdersLoading(true);
    setOrdersError(null);

    try {
      const response = await fetch(`${apiBaseUrl}/api/orders`);
      if (!response.ok) throw new Error(`${t('api_error')}: ${response.status}`);

      const data = (await response.json()) as Order[];
      setOrders(data);
      setProductionDrafts((current) => {
        const next = { ...current };

        data.forEach((order) => {
          if (!next[order.id]) {
            next[order.id] = {
              cycle_time: String(order.cycle_time ?? ''),
              quantity: String(order.quantity ?? ''),
              current_quantity: String(order.current_quantity ?? 0),
            };
          }
        });

        return next;
      });
      setOrdersLoaded(true);
    } catch (error) {
      setOrdersError(
        error instanceof Error ? error.message : t('unknown_error')
      );
    } finally {
      setIsOrdersLoading(false);
    }
  };

  const openStatusModal = (machine: Machine) => {
    setSelectedMachine(machine);
    setNewStatus(machine.status);
    setProblemDescription(machine.problem_description || '');
    setShowModal(true);
  };

  const openIssueModal = (machine: DashboardMachine, target: IssueTarget = 'machine') => {
    setSelectedMachine(machine);
    setIssueTarget(target);

    if (target === 'machine') {
      setNewStatus(machine.status);
      setProblemDescription(machine.problem_description || '');
    } else if (target === 'mould') {
      const mould = machine.currentOrder?.mould;
      setNewStatus(mould?.status || 'active');
      setProblemDescription(mould?.problem_description || '');
    } else {
      setNewStatus(machine.currentOrder?.product?.is_active ? 'active' : 'inactive');
      setProblemDescription('');
    }

    setShowModal(true);
  };

  const updateMachineStatus = async () => {
    if (!selectedMachine) return;
    setIsUpdating(true);

    try {
      let response: Response;

      if (issueTarget === 'machine') {
        response = await fetch(`${apiBaseUrl}/api/machines/${selectedMachine.id}/status`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ status: newStatus, problem_description: problemDescription }),
        });

        if (!response.ok) throw new Error(`${t('error')}: ${response.status}`);
        const updated = (await response.json()) as Machine;
        setMachines(machines.map(m => (m.id === updated.id ? updated : m)));
      } else if (issueTarget === 'mould') {
        const mouldId = selectedMachine.currentOrder?.mould_id;
        if (!mouldId) throw new Error(t('unknown_error'));

        response = await fetch(`${apiBaseUrl}/api/moulds/${mouldId}/status`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ status: newStatus, problem_description: problemDescription }),
        });

        if (!response.ok) throw new Error(`${t('error')}: ${response.status}`);
        const updated = (await response.json()) as Mould;
        setOrders(orders.map(order => order.mould_id === updated.id ? { ...order, mould: { ...updated } } : order));
        setMoulds(moulds.map(m => (m.id === updated.id ? updated : m)));
      } else {
        const productId = selectedMachine.currentOrder?.product_id;
        if (!productId) throw new Error(t('unknown_error'));

        response = await fetch(`${apiBaseUrl}/api/products/${productId}/status`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ status: newStatus }),
        });

        if (!response.ok) throw new Error(`${t('error')}: ${response.status}`);
        const updated = (await response.json()) as Product;
        setOrders(orders.map(order => order.product_id === updated.id ? { ...order, product: { ...updated } } : order));
        setProducts(products.map(p => (p.id === updated.id ? updated : p)));
      }

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

  const loadMoulds = async () => {
    setIsMouldsLoading(true);
    setMouldsError(null);
    try {
      const response = await fetch(`${apiBaseUrl}/api/moulds`);
      if (!response.ok) throw new Error(`${t('api_error')}: ${response.status}`);
      const data = (await response.json()) as Mould[];
      setMoulds(data);
      setMouldsLoaded(true);
    } catch (error) {
      setMouldsError(error instanceof Error ? error.message : t('unknown_error'));
    } finally {
      setIsMouldsLoading(false);
    }
  };

  const dashboardMachines = useMemo<DashboardMachine[]>(() => {
    return machines.map((machine) => {
      const currentOrder = orders.find(
        (order) => order.machine_id === machine.id && order.status === 'in_progress'
      ) ?? null;

      return { ...machine, currentOrder };
    });
  }, [machines, orders]);

  const activeOrdersCount = useMemo(
    () => orders.filter((order) => order.status === 'in_progress').length,
    [orders]
  );

  const getDashboardStatusColor = (active: boolean) => (active ? '#22c55e' : '#999999');

  const getDashboardStatusLabel = (active: boolean) =>
    active ? t('machine_status_active') : t('machine_status_inactive');

  const getIssueStatusColor = (target: IssueTarget, status: string) => {
    if (target === 'product') return status === 'active' ? '#22c55e' : '#999999';

    switch (status) {
      case 'active':
        return '#22c55e';
      case 'standby':
      case 'stopped':
        return '#999999';
      case 'warning':
        return '#f59e0b';
      case 'broken':
      case 'needs_repair':
        return '#ef4444';
      case 'under_repair':
        return '#3b82f6';
      default:
        return '#999999';
    }
  };

  const getIssueStatusLabel = (target: IssueTarget, status: string) => {
    if (target === 'product') {
      return status === 'active' ? t('status_active') : t('status_inactive');
    }

    switch (status) {
      case 'active':
        return t('status_active');
      case 'stopped':
        return t('status_stopped');
      case 'standby':
        return t('mould_status_standby');
      case 'warning':
        return t('status_warning');
      case 'broken':
        return t('status_broken');
      case 'needs_repair':
        return t('mould_status_needs_repair');
      case 'under_repair':
        return t('mould_status_under_repair');
      default:
        return status;
    }
  };

  const getIssueStatuses = (target: IssueTarget) => {
    if (target === 'machine') return ['active', 'stopped', 'warning', 'broken'];
    if (target === 'mould') return ['active', 'standby', 'warning', 'needs_repair', 'under_repair'];
    return ['active', 'inactive'];
  };

  const requiresProblemDescription = (target: IssueTarget, status: string) => {
    if (target === 'machine') return ['warning', 'broken'].includes(status);
    if (target === 'mould') return ['warning', 'needs_repair'].includes(status);
    return false;
  };

  const getProductionDraft = (order: Order) => {
    return productionDrafts[order.id] ?? {
      cycle_time: String(order.cycle_time ?? ''),
      quantity: String(order.quantity ?? ''),
      current_quantity: String(order.current_quantity ?? 0),
    };
  };

  const updateProductionDraft = (orderId: number, field: 'cycle_time' | 'quantity' | 'current_quantity', value: string) => {
    setProductionDrafts((current) => ({
      ...current,
      [orderId]: {
        ...current[orderId],
        [field]: value,
      },
    }));
  };

  const isWorkingTime = (date: Date) => {
    const day = date.getDay();
    const hour = date.getHours() + date.getMinutes() / 60;

    if (day === 0) return false;
    if (day === 6) return hour < 5;
    if (day === 5) return true;
    return hour >= 7;
  };

  const nextWorkingStart = (date: Date) => {
    const next = new Date(date);

    if (date.getDay() === 6) {
      next.setDate(date.getDate() + 2);
      next.setHours(7, 0, 0, 0);
      return next;
    }

    if (date.getDay() === 0) {
      next.setDate(date.getDate() + 1);
      next.setHours(7, 0, 0, 0);
      return next;
    }

    if (date.getHours() < 7) {
      next.setHours(7, 0, 0, 0);
      return next;
    }

    return next;
  };

  const workingIntervalEnd = (date: Date) => {
    const end = new Date(date);

    if (date.getDay() === 6 && date.getHours() < 5) {
      end.setHours(5, 0, 0, 0);
      return end;
    }

    const daysUntilSaturday = (6 - date.getDay() + 7) % 7 || 7;
    end.setDate(date.getDate() + daysUntilSaturday);
    end.setHours(5, 0, 0, 0);
    return end;
  };

  const addWorkingSeconds = (start: Date, seconds: number) => {
    let current = new Date(start);
    let remaining = Math.max(0, seconds);

    while (remaining > 0) {
      if (!isWorkingTime(current)) {
        current = nextWorkingStart(current);
        continue;
      }

      const intervalEnd = workingIntervalEnd(current);
      const available = Math.max(0, (intervalEnd.getTime() - current.getTime()) / 1000);
      const consumed = Math.min(available, remaining);
      current = new Date(current.getTime() + consumed * 1000);
      remaining -= consumed;

      if (remaining > 0) {
        current = nextWorkingStart(current);
      }
    }

    return current;
  };

  const workingSecondsBetween = (from: Date, to: Date) => {
    if (to <= from) return 0;

    let current = new Date(from);
    let total = 0;

    while (current < to) {
      if (!isWorkingTime(current)) {
        current = nextWorkingStart(current);
        continue;
      }

      const intervalEnd = workingIntervalEnd(current);
      const segmentEnd = intervalEnd < to ? intervalEnd : to;
      total += Math.max(0, (segmentEnd.getTime() - current.getTime()) / 1000);

      if (segmentEnd >= to) break;
      current = nextWorkingStart(segmentEnd);
    }

    return total;
  };

  const formatDuration = (seconds: number) => {
    const safeSeconds = Math.max(0, Math.round(seconds));
    const hours = Math.floor(safeSeconds / 3600);
    const minutes = Math.floor((safeSeconds % 3600) / 60);
    return `${hours}h ${minutes}m`;
  };

  const saveProductionData = async (order: Order) => {
    const draft = getProductionDraft(order);
    const cycleTime = Number(draft.cycle_time);
    const quantity = Number(draft.quantity);
    const currentQuantity = Number(draft.current_quantity);

    if (!cycleTime || !quantity || currentQuantity < 0) {
      Alert.alert(t('error'), t('unknown_error'));
      return;
    }

    try {
      const response = await fetch(`${apiBaseUrl}/api/orders/${order.id}/production-data`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          cycle_time: cycleTime,
          quantity,
          current_quantity: currentQuantity,
        }),
      });

      if (!response.ok) throw new Error(`${t('error')}: ${response.status}`);

      const updated = (await response.json()) as Order;
      setOrders((current) => current.map((item) => (item.id === updated.id ? updated : item)));
      setProductionDrafts((current) => ({
        ...current,
        [updated.id]: {
          cycle_time: String(updated.cycle_time ?? ''),
          quantity: String(updated.quantity ?? ''),
          current_quantity: String(updated.current_quantity ?? 0),
        },
      }));
      Alert.alert(t('success'), t('production_data_saved'));
    } catch (error) {
      Alert.alert(t('error'), error instanceof Error ? error.message : t('unknown_error'));
    }
  };

  const getOrderTracking = (
    order: Order,
    draft?: { cycle_time: string; quantity: string; current_quantity: string }
  ) => {
    const startedAt = order.started_at ? new Date(order.started_at) : null;
    const mouldCount = order.mould?.cavity_count ?? 1;
    const draftQuantity = Number(draft?.quantity ?? order.quantity ?? 0);
    const draftCycleTime = Number(draft?.cycle_time ?? order.cycle_time ?? 0);
    const draftCurrentQuantity = Number(draft?.current_quantity ?? order.current_quantity ?? 0);
    const totalPieces = Math.max(1, Number.isFinite(draftQuantity) ? draftQuantity : Number(order.quantity ?? 0));
    const totalCycles = Math.max(1, Math.ceil(totalPieces / Math.max(1, mouldCount)));
    const cycleTime = Math.max(1, Number.isFinite(draftCycleTime) ? draftCycleTime : Number(order.cycle_time ?? 0));
    const totalSeconds = totalCycles * cycleTime;
    const currentPiecesRaw = Number.isFinite(draftCurrentQuantity)
      ? draftCurrentQuantity
      : Number(order.current_quantity ?? 0);
    const currentPieces = Math.max(0, Math.min(totalPieces, currentPiecesRaw));
    const remainingPieces = Math.max(0, totalPieces - currentPieces);
    const remainingCycles = Math.max(0, Math.ceil(remainingPieces / Math.max(1, mouldCount)));
    const remainingSeconds = remainingCycles * cycleTime;
    const now = new Date();
    const elapsedSeconds = startedAt ? workingSecondsBetween(startedAt, now) : 0;
    const theoreticalProgress = Math.min(1, totalSeconds > 0 ? elapsedSeconds / totalSeconds : 0);
    const actualProgress = Math.min(1, totalPieces > 0 ? currentPieces / totalPieces : 0);
    const theoreticalEta = startedAt ? addWorkingSeconds(startedAt, totalSeconds) : null;
    const actualEta = startedAt ? addWorkingSeconds(now, remainingSeconds) : null;
    const delaySeconds = theoreticalEta && actualEta ? Math.max(0, (actualEta.getTime() - theoreticalEta.getTime()) / 1000) : 0;

    return {
      startedAt,
      mouldCount,
      totalPieces,
      totalCycles,
      totalSeconds,
      currentPieces,
      remainingPieces,
      remainingCycles,
      remainingSeconds,
      theoreticalProgress,
      actualProgress,
      theoreticalEta,
      actualEta,
      delaySeconds,
    };
  };

  const formatEta = (date: Date | null) => {
    if (!date) return '-';
    return `${date.toLocaleDateString()} ${date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
  };

  const percentWidth = (value: number) => {
    const bounded = Math.max(0, Math.min(1, value));
    return `${Math.max(4, Math.round(bounded * 100))}%`;
  };

  const loadProducts = async () => {
    setIsProductsLoading(true);
    setProductsError(null);

    try {
      const response = await fetch(`${apiBaseUrl}/api/products`);
      if (!response.ok) throw new Error(`${t('api_error')}: ${response.status}`);

      const data = (await response.json()) as Product[];
      setProducts(data);
      setProductsLoaded(true);
    } catch (error) {
      setProductsError(error instanceof Error ? error.message : t('unknown_error'));
    } finally {
      setIsProductsLoading(false);
    }
  };

  const openMouldStatusModal = (mould: Mould) => {
    setSelectedMould(mould);
    setNewMouldStatus(mould.status);
    setMouldProblemDescription(mould.problem_description || '');
    setShowMouldModal(true);
  };

  const updateMouldStatus = async () => {
    if (!selectedMould) return;
    setIsMouldUpdating(true);
    try {
      const response = await fetch(`${apiBaseUrl}/api/moulds/${selectedMould.id}/status`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          status: newMouldStatus,
          problem_description: mouldProblemDescription,
        }),
      });
      if (!response.ok) throw new Error(`${t('error')}: ${response.status}`);
      const updated = (await response.json()) as Mould;
      setMoulds(moulds.map(m => m.id === updated.id ? updated : m));
      setShowMouldModal(false);
      Alert.alert(t('success'), t('mould_status_updated'));
    } catch (error) {
      Alert.alert(t('error'), error instanceof Error ? error.message : t('unknown_error'));
    } finally {
      setIsMouldUpdating(false);
    }
  };

  const getMouldStatusColor = (status: string) => {
    switch (status) {
      case 'active':       return '#22c55e';
      case 'standby':      return '#999999';
      case 'warning':      return '#f59e0b';
      case 'needs_repair': return '#ef4444';
      case 'under_repair': return '#3b82f6';
      default:             return '#999999';
    }
  };

  const getMouldStatusLabel = (status: string) => {
    switch (status) {
      case 'active':       return t('mould_status_active');
      case 'standby':      return t('mould_status_standby');
      case 'warning':      return t('mould_status_warning');
      case 'needs_repair': return t('mould_status_needs_repair');
      case 'under_repair': return t('mould_status_under_repair');
      default:             return status;
    }
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
      <View style={styles.navTitleGroup}>
        <Text style={styles.navTitle}>{t('app_title')}</Text>
        <Text style={styles.navSubtitle}>{t('dashboard_title')}</Text>
      </View>

      <View style={styles.navRightGroup}>
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

        <Pressable style={styles.menuButton} onPress={() => setShowMenu(true)}>
          <Text style={styles.menuButtonText}>☰</Text>
        </Pressable>
      </View>
    </View>
  );

  const HomeScreen = () => (
    <ScrollView contentContainerStyle={styles.homeContainer}>
      <Text style={styles.homeTitle}>{t('dashboard_title')}</Text>
      <Text style={styles.homeSubtitle}>{t('dashboard_subtitle')}</Text>

      <View style={styles.homeInfo}>
        <Text style={styles.homeInfoTitle}>Papirus</Text>
        <Text style={styles.homeInfoText}>
          Injekciós fröccsöntési gyárak intelligens kezelésére kifejlesztett integrált vállalatirányítási rendszer.
        </Text>
      </View>
    </ScrollView>
  );

  const MouldsScreen = () => (
    <View style={styles.container}>
      {isMouldsLoading && !mouldsLoaded && <ActivityIndicator size="large" color="#0f766e" />}
      {mouldsError ? <Text style={styles.errorText}>{mouldsError}</Text> : null}
      {mouldsLoaded && !isMouldsLoading && moulds.length === 0 ? (
        <Text style={styles.emptyText}>{t('no_moulds')}</Text>
      ) : null}
      <FlatList
        data={moulds}
        keyExtractor={(item) => item.id.toString()}
        renderItem={({ item }) => (
          <View style={styles.machineCard}>
            <View style={styles.machineHeader}>
              <Text style={styles.machineName}>{item.name}</Text>
              <View style={[styles.statusBadge, { backgroundColor: getMouldStatusColor(item.status) }]}>
                <Text style={styles.statusBadgeText}>{getMouldStatusLabel(item.status)}</Text>
              </View>
            </View>
            {item.product && (
              <Text style={styles.machineMeta}>
                {t('product')}: {item.product.name}
              </Text>
            )}
            <Text style={styles.machineMeta}>
              {t('mould_number')}: {item.number}
            </Text>
            <Text style={styles.machineMeta}>
              {t('cavity_count')}: {item.cavity_count ?? '-'}
            </Text>
            <Text style={styles.machineMeta}>
              {t('opening')}: {item.open_mm ?? '-'} {t('mm')}
            </Text>
            {item.problem_description && (
              <Text style={styles.problemText}>
                {t('problem')}: {item.problem_description}
              </Text>
            )}
            <View style={styles.buttonGroup}>
              <Pressable
                style={[styles.actionButton, { backgroundColor: '#0f766e' }]}
                onPress={() => openMouldStatusModal(item)}
              >
                <Text style={styles.actionButtonText}>{t('change_status')}</Text>
              </Pressable>
            </View>
          </View>
        )}
        contentContainerStyle={styles.listContent}
      />
    </View>
  );

  const renderMachinesScreen = () => (
    <View style={styles.container}>
      {(isLoading || isOrdersLoading) && !hasLoaded && !ordersLoaded && <ActivityIndicator size="large" color="#0f766e" />}

      {errorMessage || ordersError ? <Text style={styles.errorText}>{errorMessage || ordersError}</Text> : null}

      {hasLoaded && ordersLoaded && !isLoading && !isOrdersLoading && machines.length === 0 ? (
        <Text style={styles.emptyText}>{t('no_machines')}</Text>
      ) : null}

      <FlatList
        data={dashboardMachines}
        keyExtractor={(item) => item.id.toString()}
        renderItem={({ item }) => (
          <View style={styles.machineCard}>
            <View style={styles.machineHeader}>
              <View style={styles.machineHeaderLeft}>
                <Text style={styles.machineName}>{item.name}</Text>
                <Text style={styles.machineMeta}>
                  {t('machine_type')}: {item.type}
                </Text>
              </View>
              <View style={styles.machineHeaderRight}>
                <View style={[styles.statusBadge, { backgroundColor: getDashboardStatusColor(Boolean(item.currentOrder)) }]}>
                  <Text style={styles.statusBadgeText}>{getDashboardStatusLabel(Boolean(item.currentOrder))}</Text>
                </View>
                {item.currentOrder && (
                  <Pressable style={styles.issueButton} onPress={() => openIssueModal(item, 'machine')}>
                    <Text style={styles.issueButtonText}>⚠️</Text>
                  </Pressable>
                )}
              </View>
            </View>
            <Text style={styles.machineMeta}>
              {t('opening')}: {item.open_mm ?? '-'} {t('mm')}
            </Text>
            <Text style={styles.machineMeta}>
              {t('screw_diameter')}: {item.screw_diameter_cm ?? '-'} {t('cm')}
            </Text>
            {item.currentOrder ? (
              <View style={styles.currentOrderBox}>
                <Text style={styles.currentOrderTitle}>{t('current_order')}</Text>
                <Text style={styles.machineMeta}>
                  {t('order_number')}: {item.currentOrder.order_number}
                </Text>
                <Text style={styles.machineMeta}>
                  {t('started_at')}: {formatEta(item.currentOrder.started_at ? new Date(item.currentOrder.started_at) : null)}
                </Text>
                <Text style={styles.machineMeta}>
                  {t('product')}: {item.currentOrder.product?.name ?? '-'}
                </Text>
                <Text style={styles.machineMeta}>
                  {t('mould')}: {item.currentOrder.mould?.name ?? '-'}
                </Text>
                <Text style={styles.machineMeta}>
                  {t('cycle_time_seconds')}: {item.currentOrder.cycle_time ?? '-'}
                </Text>
                <Text style={styles.machineMeta}>
                  {t('total_quantity')}: {item.currentOrder.quantity}
                </Text>

                {(() => {
                  const draft = getProductionDraft(item.currentOrder);
                  const tracking = getOrderTracking(item.currentOrder, draft);

                  return (
                    <>
                      <View style={styles.productionInputsRow}>
                        <View style={styles.productionInputBlock}>
                          <Text style={styles.productionLabel}>{t('cycle_time_seconds')}</Text>
                          <TextInput
                            style={styles.productionInput}
                            keyboardType="numeric"
                            value={draft.cycle_time}
                            onChangeText={(value) => updateProductionDraft(item.currentOrder.id, 'cycle_time', value)}
                          />
                        </View>
                        <View style={styles.productionInputBlock}>
                          <Text style={styles.productionLabel}>{t('total_quantity')}</Text>
                          <TextInput
                            style={styles.productionInput}
                            keyboardType="numeric"
                            value={draft.quantity}
                            onChangeText={(value) => updateProductionDraft(item.currentOrder.id, 'quantity', value)}
                          />
                        </View>
                        <View style={styles.productionInputBlock}>
                          <Text style={styles.productionLabel}>{t('current_quantity')}</Text>
                          <TextInput
                            style={styles.productionInput}
                            keyboardType="numeric"
                            value={draft.current_quantity}
                            onChangeText={(value) => updateProductionDraft(item.currentOrder.id, 'current_quantity', value)}
                          />
                        </View>
                      </View>

                      <Pressable
                        style={[styles.actionButton, styles.productionSaveButton]}
                        onPress={() => saveProductionData(item.currentOrder!)}
                      >
                        <Text style={styles.actionButtonText}>{t('save_production_data')}</Text>
                      </Pressable>

                      <View style={styles.progressGroup}>
                        <Text style={styles.progressLabel}>{t('theoretical_progress')} + {t('actual_progress')}</Text>
                        <View style={styles.progressBarOuter}>
                          <View
                            style={[
                              styles.progressBarInner,
                              styles.progressBarTheoretical,
                              { width: percentWidth(tracking.theoreticalProgress) },
                            ]}
                          />
                          <View
                            style={[
                              styles.progressBarInner,
                              styles.progressBarActual,
                              { width: percentWidth(tracking.actualProgress) },
                            ]}
                          />
                        </View>
                        <View style={styles.progressLegendRow}>
                          <Text style={styles.progressLegendItem}>
                            <Text style={styles.progressLegendSwatchTheoretical}>■</Text> {t('theoretical_progress')}: {Math.round(tracking.theoreticalProgress * 100)}%
                          </Text>
                          <Text style={styles.progressLegendItem}>
                            <Text style={styles.progressLegendSwatchActual}>■</Text> {t('actual_progress')}: {Math.round(tracking.actualProgress * 100)}%
                          </Text>
                        </View>

                        <Text style={styles.delayText}>
                          {t('delay')}: {formatDuration(tracking.delaySeconds)}
                        </Text>

                        <Text style={styles.machineMeta}>
                          {t('theoretical_eta')}: {formatEta(tracking.theoreticalEta)}
                        </Text>
                        <Text style={styles.machineMeta}>
                          {t('actual_eta')}: {formatEta(tracking.actualEta)}
                        </Text>
                      </View>
                    </>
                  );
                })()}
              </View>
            ) : (
              <Text style={styles.inactiveText}>{t('no_active_order')}</Text>
            )}
            {item.problem_description && (
              <Text style={styles.problemText}>
                {t('problem')}: {item.problem_description}
              </Text>
            )}
          </View>
        )}
        contentContainerStyle={styles.listContent}
      />
    </View>
  );

  const renderProductsScreen = () => (
    <View style={styles.container}>
      {isProductsLoading && !productsLoaded && <ActivityIndicator size="large" color="#0f766e" />}

      {productsError ? <Text style={styles.errorText}>{productsError}</Text> : null}

      {productsLoaded && !isProductsLoading && products.length === 0 ? (
        <Text style={styles.emptyText}>{t('no_products')}</Text>
      ) : null}

      <FlatList
        data={products}
        keyExtractor={(item) => item.id.toString()}
        renderItem={({ item }) => (
          <View style={styles.machineCard}>
            <View style={styles.machineHeader}>
              <Text style={styles.machineName}>{item.name}</Text>
              <View style={[styles.statusBadge, { backgroundColor: item.is_active ? '#22c55e' : '#999999' }]}>
                <Text style={styles.statusBadgeText}>{item.is_active ? t('status_active') : t('status_inactive')}</Text>
              </View>
            </View>

            <Text style={styles.machineMeta}>
              {t('product_code')}: {item.code}
            </Text>

            <Text style={styles.machineMeta}>
              {t('material')}: {item.material ? `${item.material.name} (${item.material.code})` : '-'}
            </Text>

            <Text style={styles.machineMeta}>
              {t('color')}: {item.color ? `${item.color.name} (${item.color.code})` : '-'}
            </Text>

            <Text style={styles.machineMeta}>
              {t('additives')}: {item.additives && item.additives.length > 0
                ? item.additives.map(a => `${a.name} (${a.code})`).join(', ')
                : '-'}
            </Text>

            <Text style={styles.machineMeta}>
              {t('compatible_moulds')}: {item.moulds && item.moulds.length > 0
                ? item.moulds.map(m => `${m.name} (${m.number})`).join(', ')
                : '-'}
            </Text>
          </View>
        )}
        contentContainerStyle={styles.listContent}
      />
    </View>
  );

  return (
    <SafeAreaView style={styles.safeArea}>
      <Navbar />

      {currentScreen === 'machines' && renderMachinesScreen()}
      {currentScreen === 'moulds' && <MouldsScreen />}
      {currentScreen === 'products' && renderProductsScreen()}

      <Modal visible={showMenu} animationType="fade" transparent={true}>
        <Pressable style={styles.menuOverlay} onPress={() => setShowMenu(false)}>
          <View style={styles.menuDropdown}>
            <Pressable
              style={styles.menuItem}
              onPress={() => {
                setShowMenu(false);
                setCurrentScreen('machines');
                loadMachines();
                loadOrders();
              }}
            >
              <Text style={styles.menuItemText}>{t('machines_button_label')}</Text>
            </Pressable>
            <Pressable
              style={styles.menuItem}
              onPress={() => {
                setShowMenu(false);
                setCurrentScreen('moulds');
                loadMoulds();
              }}
            >
              <Text style={styles.menuItemText}>{t('moulds_button_label')}</Text>
            </Pressable>
            <Pressable
              style={styles.menuItem}
              onPress={() => {
                setShowMenu(false);
                setCurrentScreen('products');
                loadProducts();
              }}
            >
              <Text style={styles.menuItemText}>{t('products_button_label')}</Text>
            </Pressable>
          </View>
        </Pressable>
      </Modal>

      <Modal visible={showModal} animationType="slide" transparent={true}>
        <SafeAreaView style={styles.modalContainer}>
          <ScrollView style={styles.modalContent}>
            <Text style={styles.modalTitle}>{t('issue_report_title')}</Text>
            {selectedMachine && (
              <>
                <Text style={styles.modalMachineName}>{selectedMachine.name}</Text>

                <Text style={styles.modalLabel}>{t('issue_target')}:</Text>
                <View style={styles.targetButtonGroup}>
                  <Pressable
                    style={[styles.targetButton, issueTarget === 'machine' && styles.targetButtonSelected]}
                    onPress={() => openIssueModal(selectedMachine as DashboardMachine, 'machine')}
                  >
                    <Text style={styles.targetButtonText}>{t('machine')}</Text>
                  </Pressable>
                  {selectedMachine.currentOrder?.mould && (
                    <Pressable
                      style={[styles.targetButton, issueTarget === 'mould' && styles.targetButtonSelected]}
                      onPress={() => openIssueModal(selectedMachine as DashboardMachine, 'mould')}
                    >
                      <Text style={styles.targetButtonText}>{t('mould')}</Text>
                    </Pressable>
                  )}
                  {selectedMachine.currentOrder?.product && (
                    <Pressable
                      style={[styles.targetButton, issueTarget === 'product' && styles.targetButtonSelected]}
                      onPress={() => openIssueModal(selectedMachine as DashboardMachine, 'product')}
                    >
                      <Text style={styles.targetButtonText}>{t('product')}</Text>
                    </Pressable>
                  )}
                </View>

                <Text style={styles.modalLabel}>{t('new_status')}:</Text>
                {getIssueStatuses(issueTarget).map((status) => (
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
                        { backgroundColor: getIssueStatusColor(issueTarget, status) },
                      ]}
                    />
                    <Text style={styles.statusOptionText}>{getIssueStatusLabel(issueTarget, status)}</Text>
                  </Pressable>
                ))}

                {requiresProblemDescription(issueTarget, newStatus) && (
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
                      {isUpdating ? t('updating') : t('save')}
                    </Text>
                  </Pressable>
                </View>
              </>
            )}
          </ScrollView>
        </SafeAreaView>
      </Modal>

      <Modal visible={showMouldModal} animationType="slide" transparent={true}>
        <SafeAreaView style={styles.modalContainer}>
          <ScrollView style={styles.modalContent}>
            <Text style={styles.modalTitle}>{t('change_status')}</Text>
            {selectedMould && (
              <>
                <Text style={styles.modalMachineName}>{selectedMould.name}</Text>
                <Text style={styles.modalLabel}>{t('new_status')}:</Text>
                {(['active', 'standby', 'warning', 'needs_repair', 'under_repair'] as const).map((status) => (
                  <Pressable
                    key={status}
                    style={[
                      styles.statusOption,
                      newMouldStatus === status && styles.statusOptionSelected,
                    ]}
                    onPress={() => setNewMouldStatus(status)}
                  >
                    <View style={[styles.statusOptionDot, { backgroundColor: getMouldStatusColor(status) }]} />
                    <Text style={styles.statusOptionText}>{getMouldStatusLabel(status)}</Text>
                  </Pressable>
                ))}

                {['warning', 'needs_repair'].includes(newMouldStatus) && (
                  <>
                    <Text style={styles.modalLabel}>{t('problem_description')}:</Text>
                    <TextInput
                      style={styles.problemInput}
                      placeholder={t('problem_description_placeholder')}
                      value={mouldProblemDescription}
                      onChangeText={setMouldProblemDescription}
                      multiline={true}
                      numberOfLines={4}
                    />
                  </>
                )}
                <View style={styles.modalButtonGroup}>
                  <Pressable
                    style={[styles.modalButton, { backgroundColor: '#ef4444' }]}
                    onPress={() => setShowMouldModal(false)}
                    disabled={isMouldUpdating}
                  >
                    <Text style={styles.modalButtonText}>{t('cancel')}</Text>
                  </Pressable>
                  <Pressable
                    style={[styles.modalButton, { backgroundColor: '#22c55e' }]}
                    onPress={updateMouldStatus}
                    disabled={isMouldUpdating}
                  >
                    <Text style={styles.modalButtonText}>
                      {isMouldUpdating ? t('updating') : t('update')}
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
  navTitleGroup: {
    flexDirection: 'column',
    flex: 1,
    marginRight: 12,
  },
  navRightGroup: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  navTitle: {
    color: '#ffffff',
    fontSize: 18,
    fontWeight: '800',
  },
  navSubtitle: {
    color: 'rgba(255,255,255,0.85)',
    fontSize: 12,
    fontWeight: '500',
  },
  menuButton: {
    paddingVertical: 8,
    paddingHorizontal: 12,
    borderRadius: 8,
    backgroundColor: 'rgba(255, 255, 255, 0.2)',
  },
  menuButtonText: {
    color: '#ffffff',
    fontSize: 18,
    fontWeight: '800',
  },
  menuOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.15)',
    alignItems: 'flex-end',
    justifyContent: 'flex-start',
    paddingTop: 72,
    paddingRight: 16,
  },
  menuDropdown: {
    backgroundColor: '#ffffff',
    borderRadius: 14,
    paddingVertical: 8,
    minWidth: 180,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.2,
    shadowRadius: 8,
    elevation: 8,
  },
  menuItem: {
    paddingVertical: 12,
    paddingHorizontal: 16,
  },
  menuItemText: {
    fontSize: 15,
    color: '#0f172a',
    fontWeight: '600',
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
  machineHeaderLeft: {
    flex: 1,
    paddingRight: 8,
  },
  machineHeaderRight: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
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
  issueButton: {
    width: 36,
    height: 36,
    borderRadius: 18,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#fee2e2',
  },
  issueButtonText: {
    fontSize: 18,
  },
  currentOrderBox: {
    marginTop: 10,
    padding: 12,
    borderRadius: 12,
    backgroundColor: '#f8fafc',
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  currentOrderTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: '#0f766e',
    marginBottom: 6,
  },
  inactiveText: {
    marginTop: 10,
    color: '#64748b',
    fontStyle: 'italic',
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
  progressGroup: {
    marginTop: 14,
    padding: 14,
    borderRadius: 14,
    backgroundColor: '#f8fafc',
    borderWidth: 1,
    borderColor: '#cbd5e1',
  },
  progressLabel: {
    fontSize: 12,
    fontWeight: '700',
    color: '#334155',
    marginBottom: 6,
  },
  progressBarOuter: {
    position: 'relative',
    width: '100%',
    height: 16,
    backgroundColor: '#e2e8f0',
    borderRadius: 999,
    overflow: 'hidden',
    marginBottom: 4,
    borderWidth: 1,
    borderColor: '#cbd5e1',
  },
  progressBarInner: {
    position: 'absolute',
    left: 0,
    top: 0,
    height: '100%',
    borderRadius: 999,
    minWidth: 4,
  },
  progressBarTheoretical: {
    backgroundColor: '#ef4444',
    opacity: 0.7,
    zIndex: 1,
  },
  progressBarActual: {
    backgroundColor: '#2563eb',
    opacity: 0.95,
    zIndex: 2,
  },
  progressLegendRow: {
    marginTop: 8,
    marginBottom: 4,
  },
  progressLegendItem: {
    fontSize: 12,
    fontWeight: '600',
    color: '#0f172a',
    marginBottom: 2,
  },
  progressLegendSwatchTheoretical: {
    color: '#ef4444',
    fontWeight: '800',
  },
  progressLegendSwatchActual: {
    color: '#2563eb',
    fontWeight: '800',
  },
  delayText: {
    fontSize: 13,
    fontWeight: '700',
    color: '#b91c1c',
    marginTop: 8,
  },
  productionInputsRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
    marginTop: 10,
  },
  productionInputBlock: {
    flexGrow: 1,
    flexBasis: '30%',
    minWidth: 96,
  },
  productionLabel: {
    fontSize: 11,
    fontWeight: '700',
    color: '#475569',
    marginBottom: 4,
  },
  productionInput: {
    borderWidth: 1,
    borderColor: '#cbd5e1',
    borderRadius: 10,
    paddingHorizontal: 10,
    paddingVertical: 8,
    backgroundColor: '#ffffff',
    fontSize: 14,
    color: '#0f172a',
  },
  productionSaveButton: {
    marginTop: 12,
    flex: 0,
    alignSelf: 'flex-start',
    minWidth: 200,
    backgroundColor: '#0f766e',
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
  targetButtonGroup: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  targetButton: {
    paddingVertical: 10,
    paddingHorizontal: 14,
    borderRadius: 999,
    borderWidth: 1,
    borderColor: '#cbd5e1',
    backgroundColor: '#ffffff',
  },
  targetButtonSelected: {
    backgroundColor: '#ecfdf5',
    borderColor: '#0f766e',
  },
  targetButtonText: {
    color: '#0f172a',
    fontSize: 13,
    fontWeight: '600',
    textTransform: 'capitalize',
  },
});
